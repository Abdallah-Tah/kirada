<?php

namespace Tests\Feature;

use App\Models\LandlordPayoutAccount;
use App\Models\User;
use App\Services\LandlordPayoutAccountService;
use Database\Seeders\CountryCurrencySeeder;
use Database\Seeders\DemoPortfolioSeeder;
use Database\Seeders\PlanSeeder;
use Database\Seeders\RolePermissionSeeder;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandlordPayoutAccountsTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);

        $this->landlord = User::factory()->create();
        $this->landlord->assignRole('landlord');
    }

    public function test_landlord_can_open_payment_account_settings(): void
    {
        $this->actingAs($this->landlord)
            ->get(route('payout-accounts.edit'))
            ->assertOk()
            ->assertSee('Payment accounts');
    }

    public function test_non_landlord_cannot_open_payment_account_settings(): void
    {
        $tenant = User::factory()->create();
        $tenant->assignRole('tenant');

        $this->actingAs($tenant)
            ->get(route('payout-accounts.edit'))
            ->assertForbidden();
    }

    public function test_landlord_can_sync_multiple_accounts_and_choose_one_primary(): void
    {
        $accounts = app(LandlordPayoutAccountService::class)->sync($this->landlord, [
            [
                'label' => 'Rent D-Money',
                'method' => 'd_money',
                'account_number' => '77123456',
                'account_name' => 'Abdallah Mohamed',
                'instructions' => null,
                'is_active' => true,
            ],
            [
                'label' => 'Family Waafi',
                'method' => 'waafi',
                'account_number' => '77876543',
                'account_name' => 'Abdallah Mohamed',
                'instructions' => 'Include the invoice reference.',
                'is_active' => true,
            ],
        ], 1);

        $this->assertCount(2, $accounts);
        $this->assertSame(1, $accounts->where('is_primary', true)->count());
        $this->assertSame('Family Waafi', $accounts->firstWhere('is_primary', true)->label);
    }

    public function test_sync_removes_omitted_accounts_and_preserves_single_primary(): void
    {
        $first = LandlordPayoutAccount::create([
            'landlord_id' => $this->landlord->id,
            'label' => 'Old D-Money',
            'method' => 'd_money',
            'is_primary' => true,
        ]);
        $second = LandlordPayoutAccount::create([
            'landlord_id' => $this->landlord->id,
            'label' => 'Keep Waafi',
            'method' => 'waafi',
        ]);

        app(LandlordPayoutAccountService::class)->sync($this->landlord, [[
            'id' => $second->id,
            'label' => 'Main Waafi',
            'method' => 'waafi',
            'account_number' => '77000000',
            'is_active' => true,
        ]], 0);

        $this->assertDatabaseMissing('landlord_payout_accounts', ['id' => $first->id]);
        $this->assertDatabaseHas('landlord_payout_accounts', [
            'id' => $second->id,
            'label' => 'Main Waafi',
            'is_primary' => true,
        ]);
    }

    public function test_landlord_cannot_update_another_landlords_account(): void
    {
        $otherLandlord = User::factory()->create();
        $otherLandlord->assignRole('landlord');

        $foreignAccount = LandlordPayoutAccount::create([
            'landlord_id' => $otherLandlord->id,
            'label' => 'Private account',
            'method' => 'other',
        ]);

        $this->expectException(DomainException::class);

        app(LandlordPayoutAccountService::class)->sync($this->landlord, [[
            'id' => $foreignAccount->id,
            'label' => 'Tampered',
            'method' => 'other',
            'is_active' => true,
        ]], 0);
    }

    public function test_demo_seeder_copies_adna_portfolio_records(): void
    {
        $this->seed(CountryCurrencySeeder::class);
        $this->seed(PlanSeeder::class);
        $this->seed(DemoPortfolioSeeder::class);

        $landlord = User::where('email', 'abdal_cascad@hotmail.com')->firstOrFail();

        $this->assertDatabaseHas('properties', [
            'landlord_id' => $landlord->id,
            'address_line_1' => 'Lot 615 - Cite Nagad',
        ]);
        $this->assertDatabaseHas('tenants', [
            'landlord_id' => $landlord->id,
            'first_name' => 'Adna',
            'last_name' => 'Mohamoud-Rachid',
            'phone' => '77222406',
        ]);
        $this->assertDatabaseHas('leases', [
            'landlord_id' => $landlord->id,
            'monthly_rent' => 120000,
            'payment_due_day' => 5,
        ]);
    }
}
