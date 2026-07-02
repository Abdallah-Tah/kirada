<?php

namespace Tests\Feature;

use App\Livewire\RentInvoices\Index;
use App\Models\Lease;
use App\Models\Property;
use App\Models\RentInvoice;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class RentInvoiceAccessTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    private Property $property;

    private Unit $unit;

    private User $tenantUserA;

    private Tenant $tenantA;

    private User $tenantUserB;

    private Tenant $tenantB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->landlord = User::factory()->create();
        $this->landlord->assignRole('landlord');

        $this->property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Access Test Property',
            'type' => 'apartment',
            'address_line_1' => '1 Rue de la Paix',
            'city' => 'Djibouti',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'property_id' => $this->property->id,
            'unit_number' => 'A1',
            'type' => 'apartment',
            'monthly_rent' => 50000,
            'status' => 'occupied',
        ]);

        [$this->tenantUserA, $this->tenantA] = $this->makeTenant('Amina', 'One');
        [$this->tenantUserB, $this->tenantB] = $this->makeTenant('Yusuf', 'Two');

        $this->makeInvoice($this->tenantA, 'INV-202607-0001');
        $this->makeInvoice($this->tenantB, 'INV-202607-0002');
    }

    /** @return array{0: User, 1: Tenant} */
    private function makeTenant(string $first, string $last): array
    {
        $user = User::factory()->create();
        $user->assignRole('tenant');

        $tenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'user_id' => $user->id,
            'first_name' => $first,
            'last_name' => $last,
            'phone' => '+2537700000'.random_int(0, 9),
            'email' => $user->email,
            'status' => 'active',
        ]);

        return [$user, $tenant];
    }

    private function makeInvoice(Tenant $tenant, string $number): RentInvoice
    {
        $lease = Lease::create([
            'landlord_id' => $this->landlord->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_id' => $tenant->id,
            'start_date' => now()->subMonth()->toDateString(),
            'monthly_rent' => 50000,
            'payment_due_day' => 5,
            'status' => 'active',
        ]);

        return RentInvoice::create([
            'landlord_id' => $this->landlord->id,
            'lease_id' => $lease->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_id' => $tenant->id,
            'invoice_number' => $number,
            'invoice_month' => now()->startOfMonth()->toDateString(),
            'due_date' => now()->startOfMonth()->addDays(4)->toDateString(),
            'amount' => 50000,
            'status' => 'unpaid',
        ]);
    }

    public function test_tenant_can_load_the_invoice_list(): void
    {
        $this->actingAs($this->tenantUserA)
            ->get(route('rent-invoices.index'))
            ->assertOk();
    }

    public function test_tenant_sees_only_their_own_invoices(): void
    {
        Livewire::actingAs($this->tenantUserA)
            ->test(Index::class)
            ->assertSee('INV-202607-0001')
            ->assertDontSee('INV-202607-0002');
    }

    public function test_landlord_still_sees_all_their_invoices(): void
    {
        Livewire::actingAs($this->landlord)
            ->test(Index::class)
            ->assertSee('INV-202607-0001')
            ->assertSee('INV-202607-0002');
    }

    public function test_tenant_cannot_access_invoice_create_or_edit(): void
    {
        $this->actingAs($this->tenantUserA)
            ->get(route('rent-invoices.create'))
            ->assertForbidden();

        $invoice = RentInvoice::where('tenant_id', $this->tenantA->id)->firstOrFail();

        $this->actingAs($this->tenantUserA)
            ->get(route('rent-invoices.edit', $invoice))
            ->assertForbidden();
    }

    public function test_tenant_cannot_trigger_mark_overdue(): void
    {
        Livewire::actingAs($this->tenantUserA)
            ->test(Index::class)
            ->call('markOverdue')
            ->assertForbidden();
    }
}
