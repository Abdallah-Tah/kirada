<?php

namespace Tests\Feature;

use App\Livewire\Messages\Index as MessagesIndex;
use App\Mail\LandlordTeamInvitationMail;
use App\Models\LandlordTeamMembership;
use App\Models\Property;
use App\Models\Tenant;
use App\Models\User;
use App\Services\LandlordTeamService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
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

    public function test_team_member_message_picker_uses_owner_account_scope(): void
    {
        $owner = User::factory()->create(['email_verified_at' => now()]);
        $otherOwner = User::factory()->create(['email_verified_at' => now()]);
        $manager = User::factory()->create(['email_verified_at' => now()]);
        $ownerTenantUser = User::factory()->create();
        $otherTenantUser = User::factory()->create();
        $owner->assignRole('landlord');
        $otherOwner->assignRole('landlord');
        $manager->assignRole('property-manager');

        LandlordTeamMembership::create([
            'landlord_id' => $owner->id,
            'user_id' => $manager->id,
            'invited_by' => $owner->id,
            'email' => $manager->email,
            'role' => 'property-manager',
            'token_hash' => hash('sha256', 'message-picker-test'),
            'status' => 'active',
            'expires_at' => now()->addWeek(),
            'accepted_at' => now(),
        ]);

        $ownerTenant = Tenant::create([
            'landlord_id' => $owner->id,
            'user_id' => $ownerTenantUser->id,
            'first_name' => 'Owner',
            'last_name' => 'Tenant',
            'phone' => '100',
        ]);
        Tenant::create([
            'landlord_id' => $otherOwner->id,
            'user_id' => $otherTenantUser->id,
            'first_name' => 'Other',
            'last_name' => 'Tenant',
            'phone' => '200',
        ]);

        $tenants = Livewire::actingAs($manager)
            ->test(MessagesIndex::class)
            ->instance()
            ->availableTenants;

        $this->assertSame([$ownerTenant->id], $tenants->pluck('id')->all());
    }
}
