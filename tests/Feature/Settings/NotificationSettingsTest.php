<?php

namespace Tests\Feature\Settings;

use App\Models\LandlordNotificationSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class NotificationSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_landlord_can_save_notification_defaults(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');

        $this->actingAs($landlord);

        Volt::test('pages.settings.notifications')
            ->set('invoiceChannels', ['email'])
            ->set('reminderChannels', ['email'])
            ->set('autoSendInvoices', false)
            ->set('attachPdfToEmail', true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('landlord_notification_settings', [
            'landlord_id' => $landlord->id,
            'auto_send_invoices' => false,
            'attach_pdf_to_email' => true,
        ]);
        $this->assertSame(
            ['email'],
            LandlordNotificationSetting::where('landlord_id', $landlord->id)
                ->firstOrFail()
                ->invoice_channels,
        );
    }

    public function test_whatsapp_cannot_be_selected_until_bwa_is_configured(): void
    {
        config([
            'services.bwa.api_url' => null,
            'services.bwa.request_signing_secret' => null,
        ]);
        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');

        $this->actingAs($landlord);

        Volt::test('pages.settings.notifications')
            ->set('invoiceChannels', ['whatsapp'])
            ->set('reminderChannels', ['email'])
            ->call('save')
            ->assertHasErrors('invoiceChannels');

        $this->assertDatabaseMissing('landlord_notification_settings', [
            'landlord_id' => $landlord->id,
        ]);
    }

    public function test_tenant_can_grant_and_revoke_whatsapp_consent(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');
        $user = User::factory()->create();
        $user->assignRole('tenant');
        $tenant = Tenant::create([
            'landlord_id' => $landlord->id,
            'user_id' => $user->id,
            'first_name' => 'Adna',
            'last_name' => 'Mohamoud',
            'phone' => '+25377123456',
            'email' => $user->email,
            'status' => 'active',
        ]);

        $this->actingAs($user);

        Volt::test('pages.settings.tenant-notifications')
            ->set('phone', '+12074097887')
            ->set('whatsAppOptIn', true)
            ->call('save')
            ->assertHasNoErrors();

        $tenant->refresh();
        $this->assertTrue($tenant->hasWhatsAppConsent());
        $this->assertSame('tenant_settings', $tenant->whatsapp_consent_source);
        $this->assertSame('+12074097887', $tenant->phone);

        Volt::test('pages.settings.tenant-notifications')
            ->set('whatsAppOptIn', false)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertFalse($tenant->fresh()->hasWhatsAppConsent());
        $this->assertNotNull($tenant->fresh()->whatsapp_consent_revoked_at);
    }

    public function test_tenant_cannot_open_landlord_notification_settings(): void
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $this->actingAs($tenant)
            ->get(route('notifications.edit'))
            ->assertForbidden();
    }
}
