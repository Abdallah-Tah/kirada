<?php

namespace Tests\Feature;

use App\Livewire\Tenant\Contracts\Index as TenantContractIndex;
use App\Livewire\Tenant\Contracts\Show as TenantContractShow;
use App\Mail\ContractCompleted;
use App\Models\Contract;
use App\Models\ContractSignature;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\ContractService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The renter's own contract portal: they can read what binds them, sign what is
 * pending, and keep the countersigned PDF — without ever reaching the
 * landlord's management screen or another tenant's contract.
 */
class TenantContractPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    private User $tenantUser;

    private Tenant $tenant;

    private Property $property;

    private Unit $unit;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('private');

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        $this->landlord = User::factory()->create(['email_verified_at' => now()]);
        $this->landlord->assignRole('landlord');

        [$this->tenantUser, $this->tenant] = $this->makeTenant('Adna', 'Mohamoud');

        $this->property = Property::create([
            'landlord_id' => $this->landlord->id,
            'name' => 'Marina',
            'type' => 'apartment',
            'address_line_1' => '12 Avenue Hassan',
            'city' => 'Djibouti',
            'is_active' => true,
        ]);

        $this->unit = Unit::create([
            'property_id' => $this->property->id,
            'unit_number' => '3',
            'type' => 'apartment',
            'monthly_rent' => 120000,
            'status' => 'occupied',
        ]);
    }

    /** @return array{0: User, 1: Tenant} */
    private function makeTenant(string $first, string $last): array
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('tenant');

        $tenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'user_id' => $user->id,
            'first_name' => $first,
            'last_name' => $last,
            'phone' => '+2537700'.random_int(10000, 99999),
            'email' => $user->email,
            'status' => 'active',
        ]);

        return [$user, $tenant];
    }

    private function makeContract(?Tenant $tenant = null, string $status = 'sent'): Contract
    {
        return Contract::create([
            'landlord_id' => $this->landlord->id,
            'property_id' => $this->property->id,
            'unit_id' => $this->unit->id,
            'tenant_id' => ($tenant ?? $this->tenant)->id,
            'created_by' => $this->landlord->id,
            'reference' => 'KIR-BA-2026-'.random_int(1000, 9999),
            'type' => 'bail_commercial',
            'title' => 'Bail commercial — '.($tenant ?? $this->tenant)->first_name,
            'locale' => 'fr',
            'status' => $status,
            'body_html' => '<p>Article 1 — Loyer mensuel</p>',
            'sent_at' => $status === 'draft' ? null : Carbon::now(),
        ]);
    }

    private function makeSignature(Contract $contract, string $role, string $status = 'pending'): ContractSignature
    {
        return ContractSignature::create([
            'contract_id' => $contract->id,
            'party_role' => $role,
            'name' => $role === 'preneur' ? 'Adna Mohamoud' : 'Abdallah Mohamed',
            'email' => $role === 'preneur' ? $contract->tenant->email : $this->landlord->email,
            'sign_order' => $role === 'preneur' ? 2 : 1,
            'token' => bin2hex(random_bytes(16)),
            'status' => $status,
            'expires_at' => Carbon::now()->addDays(7),
            'signed_at' => $status === 'signed' ? Carbon::now() : null,
        ]);
    }

    // ── The portal ──────────────────────────────────────

    public function test_a_tenant_sees_their_contract_in_the_list(): void
    {
        $contract = $this->makeContract();
        $this->makeSignature($contract, 'preneur');

        Livewire::actingAs($this->tenantUser)
            ->test(TenantContractIndex::class)
            ->assertOk()
            ->assertSee($contract->reference)
            ->assertSee($contract->title);
    }

    public function test_the_contracts_page_is_reachable_and_linked_in_the_tenant_sidebar(): void
    {
        $this->actingAs($this->tenantUser)
            ->get(route('tenant.contracts.index'))
            ->assertOk()
            ->assertSee('My Contracts');
    }

    public function test_a_tenant_can_read_the_terms_of_their_own_contract(): void
    {
        $contract = $this->makeContract();
        $this->makeSignature($contract, 'bailleur', 'signed');
        $signature = $this->makeSignature($contract, 'preneur');

        Livewire::actingAs($this->tenantUser)
            ->test(TenantContractShow::class, ['contract' => $contract])
            ->assertOk()
            ->assertSee('Article 1 — Loyer mensuel', false)
            ->assertSee(route('contracts.sign', $signature->token), false)
            ->assertSee('1 / 2');
    }

    public function test_a_draft_contract_is_hidden_from_the_tenant(): void
    {
        $draft = $this->makeContract(status: 'draft');

        Livewire::actingAs($this->tenantUser)
            ->test(TenantContractIndex::class)
            ->assertDontSee($draft->reference);

        $this->actingAs($this->tenantUser)
            ->get(route('tenant.contracts.show', $draft))
            ->assertForbidden();
    }

    public function test_a_tenant_cannot_open_another_tenants_contract(): void
    {
        [$otherUser, $otherTenant] = $this->makeTenant('Yusuf', 'Two');
        $theirs = $this->makeContract($otherTenant);

        $this->actingAs($this->tenantUser)
            ->get(route('tenant.contracts.show', $theirs))
            ->assertForbidden();

        Livewire::actingAs($this->tenantUser)
            ->test(TenantContractIndex::class)
            ->assertDontSee($theirs->reference);

        $this->actingAs($otherUser)
            ->get(route('tenant.contracts.show', $theirs))
            ->assertOk();
    }

    public function test_a_tenant_cannot_reach_the_landlord_contract_screens(): void
    {
        $contract = $this->makeContract();

        $this->actingAs($this->tenantUser)->get(route('contracts.index'))->assertForbidden();
        $this->actingAs($this->tenantUser)->get(route('contracts.show', $contract))->assertForbidden();
    }

    public function test_the_landlord_cannot_use_the_tenant_copy_screen(): void
    {
        $contract = $this->makeContract();

        $this->actingAs($this->landlord)
            ->get(route('tenant.contracts.show', $contract))
            ->assertForbidden();
    }

    public function test_a_tenant_can_download_their_completed_contract(): void
    {
        $contract = $this->completeContract();

        $this->actingAs($this->tenantUser)
            ->get(route('tenant.contracts.download', $contract))
            ->assertOk()
            ->assertHeader('content-type', 'application/pdf');
    }

    public function test_a_tenant_cannot_download_another_tenants_contract(): void
    {
        [, $otherTenant] = $this->makeTenant('Yusuf', 'Two');
        $theirs = $this->makeContract($otherTenant);

        $this->actingAs($this->tenantUser)
            ->get(route('tenant.contracts.download', $theirs))
            ->assertForbidden();
    }

    // ── The signed copy email ───────────────────────────

    private function completeContract(): Contract
    {
        Mail::fake();

        $contract = $this->makeContract();
        $this->makeSignature($contract, 'bailleur', 'signed');
        $this->makeSignature($contract, 'preneur', 'signed');

        app(ContractService::class)->finalizeIfComplete($contract->fresh());

        return $contract->fresh();
    }

    public function test_every_party_is_emailed_the_signed_copy_on_completion(): void
    {
        Mail::fake();

        $contract = $this->makeContract();
        $this->makeSignature($contract, 'bailleur', 'signed');
        $this->makeSignature($contract, 'preneur', 'signed');

        app(ContractService::class)->finalizeIfComplete($contract->fresh());

        $this->assertSame('completed', $contract->fresh()->status);

        Mail::assertQueued(ContractCompleted::class, 2);
        Mail::assertQueued(
            ContractCompleted::class,
            fn (ContractCompleted $mail) => $mail->hasTo($this->tenantUser->email),
        );
        Mail::assertQueued(
            ContractCompleted::class,
            fn (ContractCompleted $mail) => $mail->hasTo($this->landlord->email),
        );
    }

    public function test_the_signed_copy_email_carries_the_pdf_attachment(): void
    {
        $contract = $this->completeContract();

        $mail = new ContractCompleted($contract, $contract->signatures->first());
        $attachments = $mail->attachments();

        $this->assertCount(1, $attachments);
        $this->assertNotNull($contract->document_id);
        $this->assertTrue(Storage::disk('private')->exists($contract->document->file_path));
    }

    public function test_the_signed_copy_is_emailed_only_once(): void
    {
        Mail::fake();

        $contract = $this->makeContract();
        $this->makeSignature($contract, 'bailleur', 'signed');
        $this->makeSignature($contract, 'preneur', 'signed');

        $service = app(ContractService::class);
        $service->finalizeIfComplete($contract->fresh());
        $service->finalizeIfComplete($contract->fresh());

        Mail::assertQueued(ContractCompleted::class, 2);
    }

    public function test_no_copy_is_sent_while_a_signature_is_still_pending(): void
    {
        Mail::fake();

        $contract = $this->makeContract();
        $this->makeSignature($contract, 'bailleur', 'signed');
        $this->makeSignature($contract, 'preneur');

        app(ContractService::class)->finalizeIfComplete($contract->fresh());

        $this->assertSame('sent', $contract->fresh()->status);
        Mail::assertNotQueued(ContractCompleted::class);
    }
}
