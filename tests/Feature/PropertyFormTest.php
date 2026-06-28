<?php

namespace Tests\Feature;

use App\Livewire\Properties\Create;
use App\Models\Country;
use App\Models\Currency;
use App\Models\User;
use Database\Seeders\CountryCurrencySeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PropertyFormTest extends TestCase
{
    use RefreshDatabase;

    private User $landlord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed(RolePermissionSeeder::class);
        $this->seed(CountryCurrencySeeder::class);

        $this->landlord = User::factory()->create();
        $this->landlord->assignRole('landlord');
    }

    public function test_create_form_defaults_country_and_currency(): void
    {
        $djibouti = Country::where('code', 'DJI')->firstOrFail();
        $djf = Currency::where('code', 'DJF')->firstOrFail();

        Livewire::actingAs($this->landlord)
            ->test(Create::class)
            ->assertSet('country_id', $djibouti->id)
            ->assertSet('country', 'Djibouti')
            ->assertSet('currency_id', $djf->id);
    }

    public function test_google_address_payload_fills_property_location_fields(): void
    {
        $djibouti = Country::where('code', 'DJI')->firstOrFail();
        $djf = Currency::where('code', 'DJF')->firstOrFail();

        Livewire::actingAs($this->landlord)
            ->test(Create::class)
            ->call('applyGoogleAddress', [
                'address_line_1' => 'City Nagad',
                'city' => 'Djibouti',
                'region' => 'Djibouti',
                'postal_code' => '1001',
                'country_code' => 'DJ',
                'latitude' => 11.5721,
                'longitude' => 43.1456,
            ])
            ->assertSet('address_line_1', 'City Nagad')
            ->assertSet('city', 'Djibouti')
            ->assertSet('region', 'Djibouti')
            ->assertSet('postal_code', '1001')
            ->assertSet('country_id', $djibouti->id)
            ->assertSet('country', 'Djibouti')
            ->assertSet('currency_id', $djf->id)
            ->assertSet('latitude', 11.5721)
            ->assertSet('longitude', 43.1456);
    }
}
