<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\ContractSignature;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\AttentionService;
use App\Services\DashboardMetricsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * A tenant used to be told about a pending signature by email only: the header
 * bell was hardcoded to landlord/provider counts, so every tenant permanently
 * saw "All caught up".
 */
class TenantAttentionTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    private User $tenantUser;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->landlord = User::factory()->create();
        $this->landlord->assignRole('landlord');

        $this->tenantUser = User::factory()->create(['email_verified_at' => now()]);
        $this->tenantUser->assignRole('tenant');

        $this->tenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'user_id' => $this->tenantUser->id,
            'first_name' => 'Adna',
            'last_name' => 'Mohamoud',
            'phone' => '+25377000001',
            'email' => $this->tenantUser->email,
            'status' => 'active',
        ]);
    }

    private function makeContract(string $status = 'sent'): Contract
    {
        $property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Marina',
            'type' => 'apartment',
            'address_line_1' => '12 Avenue Hassan',
            'city' => 'Djibouti',
            'is_active' => true,
        ]);

        $unit = Unit::create([
            'property_id' => $property->id,
            'unit_number' => '3',
            'type' => 'apartment',
            'monthly_rent' => 120000,
            'status' => 'occupied',
        ]);

        return Contract::create([
            'landlord_id' => $this->landlord->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->landlord->id,
            'reference' => 'KIR-BA-2026-'.random_int(1000, 9999),
            'type' => 'bail_commercial',
            'title' => 'Bail commercial — Adna Mohamoud',
            'locale' => 'fr',
            'status' => $status,
            'body_html' => '<p>Contrat</p>',
            'sent_at' => $status === 'sent' ? Carbon::now() : null,
        ]);
    }

    private function makeSignature(Contract $contract, array $attributes = []): ContractSignature
    {
        return ContractSignature::create(array_merge([
            'contract_id' => $contract->id,
            'party_role' => 'preneur',
            'name' => 'Adna Mohamoud',
            'email' => $this->tenantUser->email,
            'sign_order' => 2,
            'token' => bin2hex(random_bytes(16)),
            'status' => 'pending',
            'expires_at' => Carbon::now()->addDays(7),
        ], $attributes));
    }

    public function test_tenant_dashboard_shows_a_pending_signature_call_to_action(): void
    {
        $signature = $this->makeSignature($this->makeContract());

        $this->actingAs($this->tenantUser)
            ->get(route('tenant.dashboard'))
            ->assertOk()
            ->assertSee('1 contract awaiting your signature')
            ->assertSee('Bail commercial — Adna Mohamoud', false)
            ->assertSee(route('contracts.sign', $signature->token), false)
            ->assertSee('data-test="tenant-sign-contract"', false);
    }

    public function test_tenant_dashboard_omits_the_call_to_action_when_nothing_is_pending(): void
    {
        $this->actingAs($this->tenantUser)
            ->get(route('tenant.dashboard'))
            ->assertOk()
            ->assertDontSee('awaiting your signature')
            ->assertDontSee('data-test="tenant-sign-contract"', false);
    }

    public function test_header_bell_surfaces_the_pending_signature_for_a_tenant(): void
    {
        $signature = $this->makeSignature($this->makeContract());

        $this->actingAs($this->tenantUser)
            ->get(route('tenant.dashboard'))
            ->assertOk()
            ->assertDontSee('All caught up. Nothing needs you right now.')
            ->assertSee('data-test="attention-pending-signatures"', false)
            ->assertSee(route('contracts.sign', $signature->token), false);
    }

    public function test_header_bell_reports_all_caught_up_for_an_idle_tenant(): void
    {
        $this->actingAs($this->tenantUser)
            ->get(route('tenant.dashboard'))
            ->assertOk()
            ->assertSee('All caught up. Nothing needs you right now.');
    }

    public function test_draft_and_expired_signatures_are_not_actionable(): void
    {
        $this->makeSignature($this->makeContract('draft'));
        $this->makeSignature($this->makeContract(), ['expires_at' => Carbon::now()->subDay()]);
        $this->makeSignature($this->makeContract(), ['status' => 'signed']);

        $this->assertSame([], app(AttentionService::class)->itemsFor($this->tenantUser));

        $metrics = app(DashboardMetricsService::class)->getTenantMetrics($this->tenantUser);
        $this->assertTrue($metrics['pending_signatures']->isEmpty());
    }

    public function test_a_tenant_only_sees_their_own_pending_signature(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('tenant');

        $otherTenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'user_id' => $otherUser->id,
            'first_name' => 'Yusuf',
            'last_name' => 'Two',
            'phone' => '+25377000002',
            'email' => $otherUser->email,
            'status' => 'active',
        ]);

        $contract = $this->makeContract();
        $contract->update(['tenant_id' => $otherTenant->id]);
        $this->makeSignature($contract);

        $this->assertSame([], app(AttentionService::class)->itemsFor($this->tenantUser));
        $this->assertCount(1, app(AttentionService::class)->itemsFor($otherUser));
    }

    public function test_the_landlord_side_of_a_contract_is_not_counted_as_tenant_attention(): void
    {
        $contract = $this->makeContract();
        $this->makeSignature($contract, ['party_role' => 'bailleur', 'sign_order' => 1]);

        $this->assertSame([], app(AttentionService::class)->itemsFor($this->tenantUser));
    }
}
