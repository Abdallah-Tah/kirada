<?php

namespace Tests\Feature;

use App\Models\LandlordTeamMembership;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_landlord_search_is_limited_to_their_portfolio(): void
    {
        [$landlord, $otherLandlord] = User::factory()->count(2)->create();
        $landlord->assignRole('landlord');
        $otherLandlord->assignRole('landlord');

        $this->property($landlord, 'Kirada Riverside');
        $this->property($otherLandlord, 'Kirada Private Portfolio');

        $this->actingAs($landlord)
            ->get(route('search.index', ['q' => 'Kirada']))
            ->assertOk()
            ->assertSee('Kirada Riverside')
            ->assertDontSee('Kirada Private Portfolio');
    }

    public function test_team_member_searches_the_owning_landlord_portfolio(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');
        $manager = User::factory()->create();
        $manager->assignRole('property-manager');

        LandlordTeamMembership::create([
            'landlord_id' => $landlord->id,
            'user_id' => $manager->id,
            'invited_by' => $landlord->id,
            'email' => $manager->email,
            'role' => 'property-manager',
            'token_hash' => hash('sha256', 'global-search-team-token'),
            'status' => 'active',
            'accepted_at' => now(),
            'expires_at' => now()->addMonth(),
        ]);

        $this->property($landlord, 'Manager Search Building');

        $this->actingAs($manager)
            ->get(route('search.index', ['q' => 'Manager Search']))
            ->assertOk()
            ->assertSee('Manager Search Building');
    }

    public function test_search_requires_authentication(): void
    {
        $this->get(route('search.index', ['q' => 'test']))
            ->assertRedirect(route('login'));
    }

    private function property(User $landlord, string $name): Property
    {
        return Property::create([
            'landlord_id' => $landlord->id,
            'name' => $name,
            'type' => 'residential',
            'address_line_1' => '1 Main Street',
            'city' => 'Djibouti',
            'country' => 'Djibouti',
            'is_active' => true,
        ]);
    }
}
