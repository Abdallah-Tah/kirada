<?php

namespace Tests\Feature;

use App\Livewire\MaintenanceRequests\Show;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\User;
use App\Services\MaintenanceQuoteService;
use App\Services\SubscriptionService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Livewire\Livewire;
use Tests\TestCase;

class MaintenanceQuoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_assigned_provider_can_submit_an_itemized_quote_through_its_full_status_lifecycle(): void
    {
        [$landlord, $provider, $request] = $this->workOrder('Lifecycle');
        $service = app(MaintenanceQuoteService::class);

        $quote = $service->submitQuote($request, $provider, [
            ['description' => 'Replacement part', 'quantity' => 2, 'unit_price' => 40],
            ['description' => 'Labour', 'quantity' => 1.5, 'unit_price' => 30],
        ], 10, 'Valid for seven days.');

        $this->assertSame('125.00', $quote->subtotal);
        $this->assertSame('12.50', $quote->tax_amount);
        $this->assertSame('137.50', $quote->total);
        $this->assertSame('pending', $quote->status);

        $quote = $service->approve($quote);
        $this->assertSame('approved', $quote->status);

        $quote = $service->markInvoiced($quote);
        $this->assertSame('invoiced', $quote->status);
        $this->assertNotNull($quote->invoiced_at);

        $quote = $service->markPaid($quote);
        $this->assertSame('paid', $quote->status);
        $this->assertNotNull($quote->paid_at);
        $this->assertSame($landlord->id, $request->landlord_id);
    }

    public function test_unassigned_provider_cannot_submit_a_quote(): void
    {
        [, , $request] = $this->workOrder('Assigned');
        $otherProvider = User::factory()->create(['email_verified_at' => now()]);
        $otherProvider->assignRole('maintenance');

        $this->expectException(\DomainException::class);

        app(MaintenanceQuoteService::class)->submitQuote($request, $otherProvider, [
            ['description' => 'Unauthorized work', 'quantity' => 1, 'unit_price' => 50],
        ]);
    }

    public function test_landlord_cannot_approve_a_quote_through_an_unrelated_work_order(): void
    {
        [$firstLandlord, , $firstRequest] = $this->workOrder('First');
        [, $secondProvider, $secondRequest] = $this->workOrder('Second');

        $foreignQuote = app(MaintenanceQuoteService::class)->submitQuote($secondRequest, $secondProvider, [
            ['description' => 'Foreign quote', 'quantity' => 1, 'unit_price' => 75],
        ]);

        try {
            Livewire::actingAs($firstLandlord)
                ->test(Show::class, ['maintenanceRequest' => $firstRequest])
                ->call('approveQuote', $foreignQuote->id);

            $this->fail('A quote from another work order must not be actionable.');
        } catch (ModelNotFoundException) {
            $this->assertTrue(true);
        }

        $this->assertSame('pending', $foreignQuote->fresh()->status);
    }

    public function test_authorized_stakeholders_can_download_maintenance_quote_and_invoice_pdfs(): void
    {
        [$landlord, $provider, $request] = $this->workOrder('Download');
        $quote = app(MaintenanceQuoteService::class)->submitQuote($request, $provider, [
            ['description' => 'Replacement pump', 'quantity' => 1, 'unit_price' => 125000],
            ['description' => 'Installation labour', 'quantity' => 2, 'unit_price' => 15000],
        ], 5, 'Payment after completed work.');

        app(SubscriptionService::class)->startTrial($landlord);

        $quoteResponse = $this->actingAs($provider)
            ->get(route('maintenance-quotes.pdf', $quote));

        $quoteResponse->assertOk();
        $quoteResponse->assertHeader('Content-Type', 'application/pdf');
        $quoteResponse->assertHeader(
            'Content-Disposition',
            'attachment; filename="maintenance-quote-'.$quote->reference.'.pdf"',
        );
        $this->assertStringStartsWith('%PDF-', $quoteResponse->getContent());

        $quote = app(MaintenanceQuoteService::class)->approve($quote);
        $quote = app(MaintenanceQuoteService::class)->markInvoiced($quote);

        $invoiceResponse = $this->actingAs($landlord)
            ->get(route('maintenance-quotes.pdf', $quote));

        $invoiceResponse->assertOk();
        $invoiceResponse->assertHeader(
            'Content-Disposition',
            'attachment; filename="maintenance-invoice-'.$quote->reference.'.pdf"',
        );
        $this->assertStringStartsWith('%PDF-', $invoiceResponse->getContent());
    }

    public function test_unrelated_maintenance_user_cannot_download_a_quote_pdf(): void
    {
        [, $provider, $request] = $this->workOrder('Private');
        $quote = app(MaintenanceQuoteService::class)->submitQuote($request, $provider, [
            ['description' => 'Private scope', 'quantity' => 1, 'unit_price' => 50],
        ]);

        $stranger = User::factory()->create(['email_verified_at' => now()]);
        $stranger->assignRole('maintenance');

        $this->actingAs($stranger)
            ->get(route('maintenance-quotes.pdf', $quote))
            ->assertForbidden();
    }

    public function test_maintenance_pdf_renders_french_status_and_handles_many_long_items(): void
    {
        [$landlord, $provider, $request] = $this->workOrder('Long');
        $items = collect(range(1, 34))
            ->map(fn (int $index) => [
                'description' => "Intervention {$index} — ".str_repeat('description technique détaillée ', 6),
                'quantity' => 1.5,
                'unit_price' => 1000000 + $index,
            ])
            ->all();

        $quote = app(MaintenanceQuoteService::class)->submitQuote(
            $request,
            $provider,
            $items,
            7.5,
            null,
        )->load([
            'items',
            'currency',
            'maintenanceUser',
            'maintenanceRequest.landlord',
            'maintenanceRequest.tenant',
            'maintenanceRequest.property.currency',
            'maintenanceRequest.unit',
        ]);

        $previousLocale = App::currentLocale();
        App::setLocale('fr');

        try {
            $html = view('maintenance.quote-pdf', [
                'quote' => $quote,
                'isInvoice' => false,
                'pdfLogoPath' => public_path('brand/kirada-logo-transparent.png'),
                'pdfSupportEmail' => null,
                'pdfDocumentDate' => now()->format('d/m/Y'),
            ])->render();
        } finally {
            App::setLocale($previousLocale);
        }

        $this->assertStringContainsString('Devis de maintenance', $html);
        $this->assertStringContainsString($quote->reference, $html);
        $this->assertStringContainsString($provider->name, $html);
        $this->assertStringContainsString('En attente', $html);
        $this->assertStringContainsString('Intervention 34', $html);

        $response = $this->actingAs($provider)
            ->get(route('maintenance-quotes.pdf', $quote));

        $response->assertOk();
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertGreaterThan(5000, strlen($response->getContent()));
        $this->assertSame($landlord->id, $request->landlord_id);
    }

    /**
     * @return array{User, User, MaintenanceRequest}
     */
    private function workOrder(string $suffix): array
    {
        $landlord = User::factory()->create(['email_verified_at' => now()]);
        $provider = User::factory()->create(['email_verified_at' => now()]);
        $landlord->assignRole('landlord');
        $provider->assignRole('maintenance');

        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => "{$suffix} Property",
            'type' => 'residential',
            'address_line_1' => '1 Service Street',
            'city' => 'Djibouti',
            'country' => 'Djibouti',
            'is_active' => true,
        ]);

        $request = MaintenanceRequest::create([
            'landlord_id' => $landlord->id,
            'property_id' => $property->id,
            'title' => "{$suffix} repair",
            'description' => 'Maintenance work requiring a quote.',
            'status' => 'in_progress',
            'assigned_to' => $provider->id,
            'reported_by' => $landlord->id,
        ]);

        return [$landlord, $provider, $request];
    }
}
