<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantInvitationGuestLayoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_invitation_uses_guest_layout_without_app_navigation(): void
    {
        $landlord = User::factory()->create();
        $tenant = Tenant::create([
            'landlord_id' => $landlord->id,
            'first_name' => 'Adna',
            'last_name' => 'Mohamoud-Rachid',
            'phone' => '77222406',
            'email' => 'adna@example.com',
            'status' => 'active',
        ]);
        $invitation = TenantInvitation::create([
            'landlord_id' => $landlord->id,
            'tenant_id' => $tenant->id,
            'email' => $tenant->email,
            'phone' => $tenant->phone,
            'token' => Str::random(64),
            'status' => 'pending',
            'expires_at' => now()->addWeek(),
        ]);

        $response = $this->get(route('tenant-invitations.accept', $invitation->token));

        $response
            ->assertOk()
            ->assertSee(__('Accept Your Invitation'))
            ->assertSee('Adna Mohamoud-Rachid')
            ->assertSee('alt="Kirada"', false)
            ->assertDontSee('kirada-app-body', false)
            ->assertDontSee('kirada-sidebar', false)
            ->assertDontSee('kirada-app-header', false);
    }
}
