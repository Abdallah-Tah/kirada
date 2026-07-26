<?php

namespace Tests\Feature;

use App\Models\Currency;
use App\Models\MaintenanceRequest;
use App\Models\Property;
use App\Models\User;
use App\Services\MaintenanceReviewService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'RolePermissionSeeder']);
    }

    public function test_completed_job_accepts_exactly_one_verified_review(): void
    {
        [$landlord, $provider, $request] = $this->completedRequest();

        $review = app(MaintenanceReviewService::class)->create($landlord, $request, [
            'rating' => 5,
            'quality_rating' => 5,
            'communication_rating' => 4,
            'professionalism_rating' => 5,
            'title' => 'Excellent work',
            'comment' => 'Finished on time and left the unit clean.',
        ]);

        $this->assertSame($provider->id, $review->maintenance_user_id);
        $this->assertSame($request->id, $review->maintenance_request_id);

        $this->expectException(\DomainException::class);
        app(MaintenanceReviewService::class)->create($landlord, $request, [
            'rating' => 1,
            'quality_rating' => 1,
            'communication_rating' => 1,
            'professionalism_rating' => 1,
        ]);
    }

    public function test_open_or_foreign_job_cannot_be_reviewed(): void
    {
        [$landlord, , $request] = $this->completedRequest();
        $request->update(['status' => 'open', 'resolved_at' => null]);

        $this->expectException(\DomainException::class);
        app(MaintenanceReviewService::class)->create($landlord, $request->fresh(), [
            'rating' => 5,
            'quality_rating' => 5,
            'communication_rating' => 5,
            'professionalism_rating' => 5,
        ]);
    }

    private function completedRequest(): array
    {
        $landlord = User::factory()->create(['email_verified_at' => now()]);
        $provider = User::factory()->create(['email_verified_at' => now()]);
        $landlord->assignRole('landlord');
        $provider->assignRole('maintenance');

        $currency = Currency::create([
            'name' => 'Djiboutian Franc',
            'code' => 'DJF',
            'symbol' => 'Fdj',
            'decimals' => 0,
            'is_active' => true,
        ]);
        $property = Property::create([
            'landlord_id' => $landlord->id,
            'currency_id' => $currency->id,
            'name' => 'Review Property',
            'type' => 'residential',
            'address_line_1' => '1 Review Street',
            'city' => 'Djibouti',
            'country' => 'Djibouti',
            'is_active' => true,
        ]);

        $request = MaintenanceRequest::create([
            'landlord_id' => $landlord->id,
            'property_id' => $property->id,
            'title' => 'Repair completed',
            'description' => 'Completed maintenance work.',
            'status' => 'resolved',
            'assigned_to' => $provider->id,
            'reported_by' => $landlord->id,
            'resolved_at' => now(),
        ]);

        return [$landlord, $provider, $request];
    }
}
