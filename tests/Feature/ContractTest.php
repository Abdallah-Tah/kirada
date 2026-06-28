<?php

namespace Tests\Feature;

use App\Livewire\Contracts\Index;
use App\Mail\ContractSignatureRequest;
use App\Models\User;
use App\Services\ContractService;
use App\Services\ContractTemplateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
        $this->artisan('db:seed', ['--class' => 'CountryCurrencySeeder']);
        $this->artisan('db:seed', ['--class' => 'PlanSeeder']);
        $this->artisan('db:seed', ['--class' => 'AdminUserSeeder']);

        $this->landlord = User::where('email', 'landlord@kirada.dj')->first();
    }

    private function sampleVariables(): array
    {
        return array_merge(app(ContractTemplateService::class)->defaultVariables(), [
            'bailleur_name' => 'Jean Bailleur',
            'bailleur_email' => 'jean@example.dj',
            'preneur_name' => 'Sara Preneur',
            'preneur_email' => 'sara@example.dj',
            'premises_designation' => 'Immeuble Marina — Local 4',
            'premises_address' => '12 Avenue Hassan, Djibouti',
            'monthly_rent' => 120000,
            'annual_rent' => 1440000,
            'deposit' => 240000,
            'city_signed' => 'Djibouti',
        ]);
    }

    public function test_it_generates_a_bail_commercial_with_two_signers(): void
    {
        $contract = app(ContractService::class)->create(
            ['landlord_id' => $this->landlord->id],
            $this->sampleVariables(),
            $this->landlord,
        );

        $this->assertSame('draft', $contract->status);
        $this->assertSame('bail_commercial', $contract->type);
        $this->assertCount(2, $contract->signatures);
        $this->assertStringContainsString('CONTRAT DE BAIL COMMERCIAL', $contract->body_html);
        $this->assertStringContainsString('120 000', $contract->body_html); // formatted rent
        $this->assertEqualsCanonicalizing(
            ['bailleur', 'preneur'],
            $contract->signatures->pluck('party_role')->all(),
        );
        $this->assertNotEmpty($contract->reference);
    }

    public function test_full_signing_flow_archives_a_signed_document(): void
    {
        Storage::fake('private');

        $service = app(ContractService::class);
        $contract = $service->create(
            ['landlord_id' => $this->landlord->id],
            $this->sampleVariables(),
            $this->landlord,
        );

        $service->send($contract);
        $this->assertSame('sent', $contract->fresh()->status);

        $png = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        foreach ($contract->signatures as $signature) {
            $service->recordSignature($signature, $png, '41.2.3.4', 'TestAgent/1.0');
            $this->assertSame('signed', $signature->fresh()->status);
            $this->assertNotEmpty($signature->fresh()->signature_hash);
        }

        $contract = $contract->fresh('document');
        $this->assertSame('completed', $contract->status);
        $this->assertNotNull($contract->completed_at);
        $this->assertNotNull($contract->document_id);

        $document = $contract->document;
        $this->assertSame('lease_agreement', $document->type);
        $this->assertSame('application/pdf', $document->mime_type);
        $this->assertStringEndsWith('.pdf', $document->file_path);
        Storage::disk('private')->assertExists($document->file_path);

        // A real PDF binary starts with the %PDF- magic header.
        $bytes = Storage::disk('private')->get($document->file_path);
        $this->assertStringStartsWith('%PDF-', $bytes);
        $this->assertGreaterThan(1000, strlen($bytes));
    }

    public function test_signature_hash_binds_the_exact_signed_payload(): void
    {
        $service = app(ContractService::class);
        $contract = $service->create(
            ['landlord_id' => $this->landlord->id],
            $this->sampleVariables(),
            $this->landlord,
        );

        $signature = $contract->signatures->first();
        $png = 'data:image/png;base64,AAAA';
        $service->recordSignature($signature, $png, '10.0.0.1', 'UA', 'Jean Bailleur');

        $fresh = $signature->fresh();
        $expected = hash('sha256', implode('|', [
            $signature->contract_id,
            $signature->id,
            $signature->token,
            $fresh->signed_at->toIso8601String(),
            $png,
        ]));

        $this->assertSame($expected, $fresh->signature_hash);
        $this->assertSame('Jean Bailleur', $fresh->typed_name);
    }

    public function test_recording_rejects_a_non_image_or_oversized_payload(): void
    {
        $service = app(ContractService::class);
        $contract = $service->create(
            ['landlord_id' => $this->landlord->id],
            $this->sampleVariables(),
            $this->landlord,
        );
        $signature = $contract->signatures->first();

        $this->expectException(\InvalidArgumentException::class);
        $service->recordSignature($signature, 'not-an-image', '10.0.0.1', 'UA');
    }

    public function test_recording_rejects_an_already_signed_party(): void
    {
        $service = app(ContractService::class);
        $contract = $service->create(
            ['landlord_id' => $this->landlord->id],
            $this->sampleVariables(),
            $this->landlord,
        );
        $signature = $contract->signatures->first();
        $png = 'data:image/png;base64,AAAA';

        $service->recordSignature($signature, $png, '10.0.0.1', 'UA');

        $this->expectException(\RuntimeException::class);
        $service->recordSignature($signature->fresh(), $png, '10.0.0.1', 'UA');
    }

    public function test_send_action_toasts_without_a_class_resolution_error(): void
    {
        $contract = app(ContractService::class)->create(
            ['landlord_id' => $this->landlord->id],
            $this->sampleVariables(),
            $this->landlord,
        );

        // Exercises the Flux::toast() path in the namespaced component.
        Livewire::actingAs($this->landlord)
            ->test(Index::class)
            ->call('send', $contract->id)
            ->assertHasNoErrors();

        $this->assertSame('sent', $contract->fresh()->status);
    }

    public function test_sending_a_contract_emails_signing_links_to_signers(): void
    {
        Mail::fake();

        $service = app(ContractService::class);
        $contract = $service->create(
            ['landlord_id' => $this->landlord->id],
            $this->sampleVariables(),
            $this->landlord,
        );

        $service->send($contract);

        Mail::assertQueued(ContractSignatureRequest::class, 2);
        Mail::assertQueued(
            ContractSignatureRequest::class,
            fn (ContractSignatureRequest $mail) => $mail->hasTo('sara@example.dj'),
        );
    }

    public function test_signers_without_an_email_are_skipped(): void
    {
        Mail::fake();

        $vars = $this->sampleVariables();
        $vars['preneur_email'] = '';

        $service = app(ContractService::class);
        $contract = $service->create(
            ['landlord_id' => $this->landlord->id],
            $vars,
            $this->landlord,
        );

        $service->send($contract);

        // Only the bailleur has an address.
        Mail::assertQueued(ContractSignatureRequest::class, 1);
    }

    public function test_guests_cannot_access_contracts_index(): void
    {
        $this->get(route('contracts.index'))->assertRedirect(route('login'));
    }

    public function test_landlord_can_view_contracts_index(): void
    {
        $this->actingAs($this->landlord)
            ->get(route('contracts.index'))
            ->assertOk();
    }

    public function test_landlord_can_view_the_generate_page(): void
    {
        $this->actingAs($this->landlord)
            ->get(route('contracts.create'))
            ->assertOk()
            ->assertSee('Generate Contract');
    }

    public function test_landlord_can_view_a_contract_show_page(): void
    {
        $contract = app(ContractService::class)->create(
            ['landlord_id' => $this->landlord->id],
            $this->sampleVariables(),
            $this->landlord,
        );

        $this->actingAs($this->landlord)
            ->get(route('contracts.show', $contract))
            ->assertOk()
            ->assertSee($contract->reference);
    }

    public function test_landlord_cannot_view_another_landlords_contract(): void
    {
        $other = User::create([
            'name' => 'Other Landlord',
            'email' => 'other-landlord@example.dj',
            'password' => bcrypt('secret'),
        ]);
        $other->assignRole('landlord');

        $contract = app(ContractService::class)->create(
            ['landlord_id' => $other->id],
            $this->sampleVariables(),
            $other,
        );

        $this->actingAs($this->landlord)
            ->get(route('contracts.show', $contract))
            ->assertForbidden();
    }

    public function test_public_signing_page_renders_for_a_valid_token(): void
    {
        $contract = app(ContractService::class)->create(
            ['landlord_id' => $this->landlord->id],
            $this->sampleVariables(),
            $this->landlord,
        );

        $token = $contract->signatures->first()->token;

        $this->get(route('contracts.sign', $token))
            ->assertOk()
            ->assertSee('CONTRAT DE BAIL COMMERCIAL');
    }

    public function test_signing_page_404s_for_unknown_token(): void
    {
        $this->get(route('contracts.sign', 'nope-not-a-real-token'))->assertNotFound();
    }
}
