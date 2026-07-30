<?php

namespace Tests\Feature;

use App\Mail\LandlordTeamInvitationMail;
use App\Models\LandlordTeamMembership;
use App\Models\Property;
use App\Models\User;
use App\Services\LandlordTeamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LandlordTeamTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_owner_can_invite_a_property_manager_who_joins_one_landlord_account(): void
    {
        Mail::fake();
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $owner->assignRole('landlord');

        $membership = app(LandlordTeamService::class)->invite(
            $owner,
            'manager@example.com',
            'property-manager',
        );

        Mail::assertQueued(LandlordTeamInvitationMail::class);
        $this->assertTrue($membership->isPending());

        $member = app(LandlordTeamService::class)->accept(
            $membership,
            'Property Manager',
            'Password123!',
        );

        $this->assertTrue($member->hasRole('property-manager'));
        $this->assertSame($owner->id, $member->landlordAccountId());
        $this->assertTrue($membership->fresh()->isActive());
    }

    public function test_owner_can_open_team_workspace_and_understand_each_role(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $owner->assignRole('landlord');

        $response = $this->actingAs($owner)->get(route('property-team.index'));

        $response
            ->assertOk()
            ->assertSee('Property Team')
            ->assertSee('Choose the right role')
            ->assertSee('Landlord Admin')
            ->assertSee('Property Manager')
            ->assertSee('Accountant')
            ->assertSee('Viewer')
            ->assertSee('Send invitation');
    }

    public function test_owner_team_navigation_survives_stale_team_permissions(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $owner->assignRole('landlord');

        Role::findByName('landlord')->revokePermissionTo([
            'team.view',
            'team.invite',
            'team.manage',
        ]);

        $response = $this->actingAs($owner)->get(route('property-team.index'));

        $response
            ->assertOk()
            ->assertSee('Property Team')
            ->assertSee('Invite a team member');
    }

    public function test_team_member_cannot_join_a_second_landlord_account(): void
    {
        Mail::fake();
        $firstOwner = User::factory()->create(['email_verified_at' => now()]);
        $secondOwner = User::factory()->create(['email_verified_at' => now()]);
        $firstOwner->assignRole('landlord');
        $secondOwner->assignRole('landlord');

        $membership = app(LandlordTeamService::class)->invite($firstOwner, 'staff@example.com', 'viewer');
        app(LandlordTeamService::class)->accept($membership, 'Staff', 'Password123!');

        $this->expectException(\DomainException::class);
        app(LandlordTeamService::class)->invite($secondOwner, 'staff@example.com', 'viewer');
    }

    public function test_property_manager_can_update_only_their_landlord_property(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $otherOwner = User::factory()->create(['email_verified_at' => now()]);
        $manager = User::factory()->create(['email_verified_at' => now()]);
        $owner->assignRole('landlord');
        $otherOwner->assignRole('landlord');
        $manager->assignRole('property-manager');

        LandlordTeamMembership::create([
            'landlord_id' => $owner->id,
            'user_id' => $manager->id,
            'invited_by' => $owner->id,
            'email' => $manager->email,
            'role' => 'property-manager',
            'token_hash' => hash('sha256', 'team-test'),
            'status' => 'active',
            'expires_at' => now()->addWeek(),
            'accepted_at' => now(),
        ]);

        $ownProperty = Property::make(['landlord_id' => $owner->id]);
        $otherProperty = Property::make(['landlord_id' => $otherOwner->id]);

        $this->assertTrue($manager->can('update', $ownProperty));
        $this->assertFalse($manager->can('update', $otherProperty));
    }
}
