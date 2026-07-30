<?php

namespace Tests\Feature;

use App\Models\AuditEvent;
use App\Models\Property;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_portfolio_changes_are_attributed_to_the_actor(): void
    {
        $landlord = User::factory()->create();
        $landlord->assignRole('landlord');

        $this->actingAs($landlord);

        $property = Property::create([
            'landlord_id' => $landlord->id,
            'name' => 'Harbor House',
            'type' => 'apartment',
            'address_line_1' => '1 Main Street',
            'city' => 'Djibouti',
            'country' => 'Djibouti',
            'is_active' => true,
        ]);

        $audit = AuditEvent::query()
            ->where('auditable_type', $property->getMorphClass())
            ->where('auditable_id', $property->id)
            ->firstOrFail();

        $this->assertSame($landlord->id, $audit->landlord_id);
        $this->assertSame($landlord->id, $audit->actor_id);
        $this->assertSame('created', $audit->event);
        $this->assertSame('Harbor House', $audit->new_values['name']);
        $this->assertArrayNotHasKey('description', $audit->new_values);
    }

    public function test_landlord_only_sees_their_own_portfolio_activity(): void
    {
        [$first, $second] = User::factory()->count(2)->create();
        $first->assignRole('landlord');
        $second->assignRole('landlord');

        AuditEvent::create([
            'landlord_id' => $first->id,
            'actor_id' => $first->id,
            'event' => 'updated',
            'auditable_type' => Property::class,
            'auditable_id' => 10,
            'new_values' => ['name' => 'Visible portfolio'],
        ]);
        AuditEvent::create([
            'landlord_id' => $second->id,
            'actor_id' => $second->id,
            'event' => 'updated',
            'auditable_type' => Property::class,
            'auditable_id' => 11,
            'new_values' => ['name' => 'Hidden portfolio'],
        ]);

        $this->actingAs($first)
            ->get(route('audit.index'))
            ->assertOk()
            ->assertSee('#10', false)
            ->assertDontSee('#11', false);
    }

    public function test_tenant_cannot_open_audit_center(): void
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $this->actingAs($tenant)
            ->get(route('audit.index'))
            ->assertForbidden();
    }
}
