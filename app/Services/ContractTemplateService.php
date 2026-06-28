<?php

namespace App\Services;

use App\Models\Lease;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\View;

/**
 * Provides contract templates (e.g. the French "bail commercial"), builds the
 * variable set that fills them, and renders the contract body to HTML.
 *
 * Templates live in resources/views/contracts/templates/{type}.blade.php and
 * receive a single `$v` array of escaped variables.
 */
class ContractTemplateService
{
    /**
     * Machine type => human label. Add new templates here as they are authored.
     *
     * @return array<string, string>
     */
    public function availableTemplates(): array
    {
        return [
            'bail_commercial' => 'Bail commercial (FR) — Commercial lease',
        ];
    }

    public function isSupported(string $type): bool
    {
        return array_key_exists($type, $this->availableTemplates());
    }

    /**
     * The default, empty variable set for a template — also documents the
     * full list of fields each contract type expects.
     *
     * @return array<string, mixed>
     */
    public function defaultVariables(): array
    {
        return [
            'city_signed' => '',
            'date_signed' => Carbon::now()->format('Y-m-d'),
            // Bailleur (landlord)
            'bailleur_name' => '',
            'bailleur_email' => '',
            'bailleur_address' => '',
            // Preneur (tenant)
            'preneur_name' => '',
            'preneur_email' => '',
            'preneur_address' => '',
            'preneur_id' => '',
            // Premises
            'premises_designation' => '',
            'premises_address' => '',
            'premises_area' => '',
            'destination' => 'Activité commerciale',
            // Term
            'duration_years' => 9,
            'start_date' => Carbon::now()->format('Y-m-d'),
            'end_date' => '',
            // Money
            'currency' => 'DJF',
            'annual_rent' => '',
            'monthly_rent' => '',
            'charges' => '',
            'deposit' => '',
            'payment_terms' => "mensuellement et d'avance, le premier de chaque mois",
            'index_ref' => 'Indice des Loyers Commerciaux (ILC)',
            'special_conditions' => '',
        ];
    }

    /**
     * Pre-fill the variable set from an existing lease and its relations.
     *
     * @return array<string, mixed>
     */
    public function buildVariablesFromLease(Lease $lease): array
    {
        $lease->loadMissing(['landlord', 'tenant', 'property.currency', 'unit']);

        $property = $lease->property;
        $unit = $lease->unit;
        $tenant = $lease->tenant;

        $premisesAddress = $property
            ? trim(implode(', ', array_filter([
                $property->address_line_1,
                $property->address_line_2,
                $property->city,
                $property->region,
                $property->country,
            ])))
            : '';

        $designation = trim(implode(' — ', array_filter([
            $property?->name,
            $unit ? 'Unité '.$unit->unit_number : null,
        ])));

        $monthlyRent = (float) $lease->monthly_rent;

        return array_merge($this->defaultVariables(), array_filter([
            'city_signed' => $property?->city,
            'bailleur_name' => $lease->landlord?->name,
            'bailleur_email' => $lease->landlord?->email,
            'preneur_name' => $tenant?->full_name,
            'preneur_email' => $tenant?->email,
            'preneur_address' => $tenant?->address,
            'preneur_id' => $tenant?->national_id,
            'premises_designation' => $designation,
            'premises_address' => $premisesAddress,
            'premises_area' => $unit?->area_sqm ? (string) $unit->area_sqm : null,
            'currency' => $property?->currency?->code,
            'start_date' => optional($lease->start_date)->format('Y-m-d'),
            'end_date' => optional($lease->end_date)->format('Y-m-d'),
            'monthly_rent' => $monthlyRent ? (string) $monthlyRent : null,
            'annual_rent' => $monthlyRent ? (string) ($monthlyRent * 12) : null,
            'deposit' => $lease->security_deposit ? (string) $lease->security_deposit : null,
            'special_conditions' => $lease->notes,
        ], fn ($value) => $value !== null && $value !== ''));
    }

    /**
     * Render a contract type to an HTML body string from its Blade template.
     *
     * @param  array<string, mixed>  $variables
     */
    public function render(string $type, array $variables): string
    {
        if (! $this->isSupported($type)) {
            throw new \InvalidArgumentException("Unsupported contract type: {$type}");
        }

        $view = 'contracts.templates.'.str_replace('_', '-', $type);

        return View::make($view, ['v' => array_merge($this->defaultVariables(), $variables)])->render();
    }
}
