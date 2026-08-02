<?php

namespace Tests\Feature;

use App\Livewire\Contracts\Sign;
use App\Models\Contract;
use App\Models\ContractSignature;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The signing link is a public bearer token, so most visitors are anonymous and
 * belong on the confirmation card. A signed-in party, however, arrived from
 * inside the app and should be handed back to it.
 */
class ContractSigningRedirectTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    private User $tenantUser;

    private Tenant $tenant;

    private Contract $contract;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->landlord = User::factory()->create(['email_verified_at' => now()]);
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

        $this->contract = Contract::create([
            'landlord_id' => $this->landlord->id,
            'property_id' => $property->id,
            'unit_id' => $unit->id,
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->landlord->id,
            'reference' => 'KIR-BA-2026-1676',
            'type' => 'bail_commercial',
            'title' => 'Bail commercial — Adna Mohamoud',
            'locale' => 'fr',
            'status' => 'sent',
            'body_html' => '<p>Contrat</p>',
            'sent_at' => Carbon::now(),
        ]);
    }

    private function signatureFor(string $role): ContractSignature
    {
        return ContractSignature::create([
            'contract_id' => $this->contract->id,
            'party_role' => $role,
            'name' => $role === 'preneur' ? 'Adna Mohamoud' : 'Abdallah Mohamed',
            'email' => $role === 'preneur' ? $this->tenantUser->email : $this->landlord->email,
            'sign_order' => $role === 'preneur' ? 2 : 1,
            'token' => bin2hex(random_bytes(16)),
            'status' => 'pending',
            'expires_at' => Carbon::now()->addDays(7),
        ]);
    }

    private function sign(ContractSignature $signature): Testable
    {
        return Livewire::test(Sign::class, ['token' => $signature->token])
            ->set('signatureData', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')
            ->set('typedName', 'Adna Mohamoud')
            ->set('agreed', true)
            ->call('sign');
    }

    public function test_an_anonymous_signer_stays_on_the_public_confirmation(): void
    {
        $signature = $this->signatureFor('preneur');

        $this->sign($signature)
            ->assertNoRedirect()
            ->assertSee(__('Signature recorded'));

        $this->assertSame('signed', $signature->fresh()->status);
        $this->assertNull(session('status'));
    }

    public function test_a_signed_in_tenant_is_returned_to_their_dashboard(): void
    {
        $signature = $this->signatureFor('preneur');

        $this->actingAs($this->tenantUser);

        $this->sign($signature)->assertRedirect(route('dashboard'));

        $this->assertSame('signed', $signature->fresh()->status);
        $this->assertSame(
            'Your signature for KIR-BA-2026-1676 has been recorded.',
            session('status'),
        );
    }

    public function test_the_tenant_dashboard_shows_the_confirmation_after_the_redirect(): void
    {
        $signature = $this->signatureFor('preneur');

        $this->actingAs($this->tenantUser);
        $this->sign($signature);

        $this->get(route('tenant.dashboard'))
            ->assertOk()
            ->assertSee('data-test="status-banner"', false)
            ->assertSee('Your signature for KIR-BA-2026-1676 has been recorded.')
            // The signed contract no longer waits on them.
            ->assertDontSee('awaiting your signature');
    }

    public function test_a_signed_in_landlord_is_returned_to_the_contract(): void
    {
        $signature = $this->signatureFor('bailleur');

        $this->actingAs($this->landlord);

        $this->sign($signature)
            ->assertRedirect(route('contracts.show', $this->contract));
    }

    public function test_a_different_signed_in_user_is_not_redirected(): void
    {
        $intruder = User::factory()->create(['email_verified_at' => now()]);
        $intruder->assignRole('tenant');

        $signature = $this->signatureFor('preneur');

        $this->actingAs($intruder);

        $this->sign($signature)
            ->assertNoRedirect()
            ->assertSee(__('Signature recorded'));
    }

    public function test_a_landlord_opening_the_tenant_link_is_not_redirected(): void
    {
        $signature = $this->signatureFor('preneur');

        $this->actingAs($this->landlord);

        $this->sign($signature)
            ->assertNoRedirect()
            ->assertSee(__('Signature recorded'));
    }

    public function test_a_tenant_opening_the_landlord_link_is_not_redirected(): void
    {
        $signature = $this->signatureFor('bailleur');

        $this->actingAs($this->tenantUser);

        $this->sign($signature)
            ->assertNoRedirect()
            ->assertSee(__('Signature recorded'));
    }
}
