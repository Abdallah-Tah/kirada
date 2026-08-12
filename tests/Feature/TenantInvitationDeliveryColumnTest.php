<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The Delivery column on the tenant invitations screen must reflect the current
 * WhatsApp status, not merely whether an error string was ever recorded.
 */
class TenantInvitationDeliveryColumnTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolePermissionSeeder::class);
    }

    #[DataProvider('statusLabels')]
    public function test_each_delivery_state_renders_its_label(string $status, string $expected): void
    {
        $landlord = $this->landlord();
        $this->invitation($landlord, $status);

        $this->actingAs($landlord)
            ->get(route('tenant-invitations.index'))
            ->assertOk()
            ->assertSee($expected);
    }

    public static function statusLabels(): array
    {
        return [
            'queued' => ['queued', 'WhatsApp queued'],
            'sent' => ['sent', 'WhatsApp sent'],
            'delivered' => ['delivered', 'WhatsApp delivered'],
            'read' => ['read', 'WhatsApp read'],
            'failed' => ['failed', 'WhatsApp failed'],
        ];
    }

    public function test_a_delivered_invitation_does_not_show_failed_despite_a_stale_error(): void
    {
        $landlord = $this->landlord();

        // A message that failed once and was later reported delivered keeps the
        // old error text on the row; the column must still read "delivered".
        $this->invitation($landlord, 'delivered', ['whatsapp_error' => '(131030) Recipient not in allowed list']);

        $response = $this->actingAs($landlord)->get(route('tenant-invitations.index'))->assertOk();

        $response->assertSee('WhatsApp delivered');
        $response->assertDontSee('WhatsApp delivery failed');
        $response->assertDontSee('WhatsApp failed');
    }

    public function test_the_status_timestamp_is_shown(): void
    {
        $landlord = $this->landlord();
        $this->invitation($landlord, 'read', [
            'whatsapp_read_at' => now()->setTime(15, 17),
        ]);

        $this->actingAs($landlord)
            ->get(route('tenant-invitations.index'))
            ->assertOk()
            ->assertSee('3:17 PM');
    }

    public function test_no_whatsapp_line_is_shown_when_there_is_no_status(): void
    {
        $landlord = $this->landlord();
        $this->invitation($landlord, null);

        $this->actingAs($landlord)
            ->get(route('tenant-invitations.index'))
            ->assertOk()
            ->assertDontSee('WhatsApp queued');
    }

    /**
     * The invitations route sits behind auth, verified and subscription, so the
     * landlord needs a live trial to reach the screen under test.
     */
    private function landlord(): User
    {
        $landlord = User::factory()->create(['email_verified_at' => now()]);
        $landlord->assignRole('landlord');

        Subscription::create([
            'user_id' => $landlord->id,
            'plan_id' => Plan::query()->value('id') ?? Plan::create([
                'name' => 'Test', 'slug' => 'test', 'price' => 0, 'interval' => 'month',
            ])->id,
            'status' => 'trialing',
            'trial_ends_at' => now()->addDays(14),
        ]);

        return $landlord->fresh();
    }

    /** @param array<string, mixed> $overrides */
    private function invitation(User $landlord, ?string $status, array $overrides = []): TenantInvitation
    {
        $tenant = Tenant::factory()->create(['landlord_id' => $landlord->id]);

        return TenantInvitation::create(array_merge([
            'landlord_id' => $landlord->id,
            'tenant_id' => $tenant->id,
            'email' => 'tenant@example.test',
            'phone' => '+12074097887',
            'delivery_channels' => ['whatsapp'],
            'token' => bin2hex(random_bytes(16)),
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
            'whatsapp_status' => $status,
        ], $overrides));
    }
}
