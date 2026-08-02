<?php

namespace Tests\Feature;

use App\Mail\ContractCompleted;
use App\Mail\ContractSignatureRequest;
use App\Mail\TenantInvitationMail;
use App\Models\Contract;
use App\Models\ContractSignature;
use App\Models\LandlordTeamMembership;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use App\Services\Bwa\BwaMessagingApi;
use App\Services\ContractService;
use App\Services\TenantInvitationService;
use App\Support\Locales;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Outbound mail and WhatsApp are written in the landlord account's language.
 *
 * The two things worth pinning down: a queue worker has no session, so the
 * locale cannot come from the request; and the recipient's own preference must
 * not override the landlord's, because the paperwork belongs to the business.
 */
class OutboundLocaleTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    private User $tenantUser;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        Storage::fake('private');

        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);

        // The landlord runs their business in French…
        $this->landlord = User::factory()->create([
            'email_verified_at' => now(),
            'preferred_language' => 'fr',
        ]);
        $this->landlord->assignRole('landlord');

        // …while the tenant reads the app in Somali.
        $this->tenantUser = User::factory()->create([
            'email_verified_at' => now(),
            'preferred_language' => 'so',
        ]);
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

        // Nothing in these tests may lean on the request locale.
        App::setLocale('en');
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
            'reference' => 'KIR-BA-2026-1676',
            'type' => 'bail_commercial',
            'title' => 'Bail commercial',
            'locale' => 'fr',
            'status' => $status,
            'body_html' => '<p>Contrat</p>',
            'sent_at' => Carbon::now(),
        ]);
    }

    private function makeSignature(Contract $contract, string $role, string $status = 'pending'): ContractSignature
    {
        return ContractSignature::create([
            'contract_id' => $contract->id,
            'party_role' => $role,
            'name' => $role === 'preneur' ? 'Adna Mohamoud' : 'Abdallah Mohamed',
            'email' => $role === 'preneur' ? $this->tenantUser->email : $this->landlord->email,
            'sign_order' => $role === 'preneur' ? 2 : 1,
            'token' => bin2hex(random_bytes(16)),
            'status' => $status,
            'expires_at' => Carbon::now()->addDays(7),
            'signed_at' => $status === 'signed' ? Carbon::now() : null,
        ]);
    }

    /** The bodies actually put on the wire by the array transport. */
    private function sentBodies(): string
    {
        $messages = app('mailer')->getSymfonyTransport()->messages();

        return collect($messages)
            ->map(fn ($sent) => $sent->getOriginalMessage()->getSubject().' '.$sent->getOriginalMessage()->toString())
            ->implode("\n");
    }

    // ── Resolution rules ────────────────────────────────

    public function test_the_landlords_preference_decides_the_language(): void
    {
        $this->assertSame('fr', Locales::forLandlord($this->landlord));
    }

    public function test_a_team_member_inherits_the_account_owners_language(): void
    {
        $manager = User::factory()->create(['preferred_language' => 'am']);
        $manager->assignRole('property-manager');

        LandlordTeamMembership::create([
            'landlord_id' => $this->landlord->id,
            'user_id' => $manager->id,
            'email' => $manager->email,
            'invited_by' => $this->landlord->id,
            'role' => 'property-manager',
            'token_hash' => hash('sha256', 'tok'),
            'status' => 'active',
            'expires_at' => now()->addDays(7),
            'accepted_at' => now(),
        ]);

        // One business, one language — not the manager's personal Amharic.
        $this->assertSame('fr', Locales::forLandlord($manager->refresh()));
    }

    public function test_an_unsupported_or_missing_language_falls_back_to_the_default(): void
    {
        $this->assertSame(Locales::DEFAULT, Locales::normalize('klingon'));
        $this->assertSame(Locales::DEFAULT, Locales::normalize(null));
        $this->assertSame(Locales::DEFAULT, Locales::forLandlord(null));
        $this->assertSame(Locales::DEFAULT, Locales::forUser(User::factory()->create(['preferred_language' => null])));
    }

    // ── Email ───────────────────────────────────────────

    public function test_the_signature_request_is_written_in_the_landlords_language(): void
    {
        $contract = $this->makeContract();
        $signature = $this->makeSignature($contract, 'preneur');

        app(ContractService::class)->sendSignatureRequest($signature->fresh());

        $bodies = $this->sentBodies();
        $this->assertStringContainsString('Signature demand', $bodies);
        $this->assertStringNotContainsString('Your signature is requested', $bodies);
    }

    public function test_the_recipients_own_language_does_not_override_the_landlords(): void
    {
        // The tenant reads the app in Somali; the contract still arrives in French.
        $this->assertSame('so', $this->tenantUser->preferred_language);

        $contract = $this->makeContract();
        $signature = $this->makeSignature($contract, 'preneur');

        app(ContractService::class)->sendSignatureRequest($signature->fresh());

        $this->assertStringContainsString('Signature demand', $this->sentBodies());
    }

    public function test_the_signed_copy_email_is_written_in_the_landlords_language(): void
    {
        $contract = $this->makeContract();
        $this->makeSignature($contract, 'bailleur', 'signed');
        $this->makeSignature($contract, 'preneur', 'signed');

        app(ContractService::class)->finalizeIfComplete($contract->fresh());

        $this->assertStringContainsString('Votre contrat est enti', $this->sentBodies());
    }

    public function test_the_tenant_invitation_is_written_in_the_landlords_language(): void
    {
        Mail::fake();

        app(TenantInvitationService::class)->createInvitation(
            $this->landlord->id,
            $this->tenant->id,
            $this->tenantUser->email,
            null,
            ['email'],
        );

        Mail::assertQueued(
            TenantInvitationMail::class,
            fn (TenantInvitationMail $mail) => $mail->locale === 'fr',
        );
    }

    public function test_switching_the_landlord_to_arabic_switches_the_email(): void
    {
        $this->landlord->forceFill(['preferred_language' => 'ar'])->save();

        Mail::fake();

        $contract = $this->makeContract();
        $signature = $this->makeSignature($contract, 'preneur');
        app(ContractService::class)->sendSignatureRequest($signature->fresh());

        Mail::assertQueued(
            ContractSignatureRequest::class,
            fn (ContractSignatureRequest $mail) => $mail->locale === 'ar',
        );
    }

    public function test_the_completed_copy_carries_the_landlord_locale_on_every_recipient(): void
    {
        Mail::fake();

        $contract = $this->makeContract();
        $this->makeSignature($contract, 'bailleur', 'signed');
        $this->makeSignature($contract, 'preneur', 'signed');

        app(ContractService::class)->finalizeIfComplete($contract->fresh());

        Mail::assertQueued(ContractCompleted::class, 2);
        Mail::assertQueued(
            ContractCompleted::class,
            fn (ContractCompleted $mail) => $mail->locale === 'fr',
        );
    }

    // ── WhatsApp ────────────────────────────────────────

    public function test_whatsapp_falls_back_to_the_configured_template_language(): void
    {
        config([
            'services.bwa.template_language' => 'fr',
            'services.bwa.template_languages' => [],
        ]);

        // No approved Somali template, so a Somali landlord keeps the default.
        $this->landlord->forceFill(['preferred_language' => 'so'])->save();

        $this->assertSame(
            'fr',
            app(BwaMessagingApi::class)->templateLanguageFor($this->landlord->refresh()),
        );
    }

    public function test_whatsapp_uses_the_approved_template_for_the_landlord_locale(): void
    {
        config([
            'services.bwa.template_language' => 'fr',
            'services.bwa.template_languages' => ['ar' => 'ar', 'en' => 'en_US'],
        ]);

        $this->landlord->forceFill(['preferred_language' => 'ar'])->save();
        $this->assertSame('ar', app(BwaMessagingApi::class)->templateLanguageFor($this->landlord->refresh()));

        $this->landlord->forceFill(['preferred_language' => 'en'])->save();
        $this->assertSame('en_US', app(BwaMessagingApi::class)->templateLanguageFor($this->landlord->refresh()));
    }
}
