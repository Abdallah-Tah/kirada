<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantInvitationService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TenantInvitationSecurityTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        $this->seed(RolePermissionSeeder::class);
        $this->landlord = User::factory()->create();
        $this->landlord->assignRole('landlord');
        $this->tenant = Tenant::create([
            'landlord_id' => $this->landlord->id,
            'first_name' => 'Adna',
            'last_name' => 'Mohamoud-Rachid',
            'phone' => '77222406',
            'email' => 'ADNA@example.com',
            'status' => 'active',
        ]);
    }

    public function test_invited_tenant_can_create_account_with_case_insensitive_matching_email(): void
    {
        $invitation = app(TenantInvitationService::class)->createInvitation(
            $this->landlord->id,
            $this->tenant->id,
            'ADNA@example.com',
            null,
        );

        $user = app(TenantInvitationService::class)->acceptInvitation(
            $invitation,
            'Adna Mohamoud-Rachid',
            'adna@example.com',
            'Strong-password-123',
        );

        $this->assertTrue($user->hasRole('tenant'));
        $this->assertSame($user->id, $this->tenant->fresh()->user_id);
        $this->assertSame('accepted', $invitation->fresh()->status);
    }

    public function test_invitation_cannot_be_redirected_to_another_email(): void
    {
        $invitation = app(TenantInvitationService::class)->createInvitation(
            $this->landlord->id,
            $this->tenant->id,
            'adna@example.com',
            null,
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('does not match');

        app(TenantInvitationService::class)->acceptInvitation(
            $invitation,
            'Attacker',
            'other@example.com',
            'Strong-password-123',
        );
    }

    public function test_existing_account_requires_its_current_password(): void
    {
        User::factory()->create(['email' => 'adna@example.com']);
        $invitation = app(TenantInvitationService::class)->createInvitation(
            $this->landlord->id,
            $this->tenant->id,
            'adna@example.com',
            null,
        );

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('current password');

        app(TenantInvitationService::class)->acceptInvitation(
            $invitation,
            'Adna',
            'adna@example.com',
            'wrong-password',
        );
    }
}
