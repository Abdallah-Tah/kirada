<?php

namespace App\Livewire\Contracts;

use App\Models\Contract;
use App\Models\Lease;
use App\Services\ContractService;
use App\Services\ContractTemplateService;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Create extends Component
{
    public string $type = 'bail_commercial';
    public ?int $lease_id = null;
    public string $title = '';

    /**
     * The editable contract variable set, prefilled from a lease.
     *
     * @var array<string, mixed>
     */
    public array $v = [];

    public function mount(ContractTemplateService $templates): void
    {
        $this->v = $templates->defaultVariables();
    }

    #[Computed]
    public function leases()
    {
        $query = Lease::query()
            ->with(['property:id,name', 'unit:id,unit_number', 'tenant:id,first_name,last_name'])
            ->latest();

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->get();
    }

    #[Computed]
    public function templateOptions(): array
    {
        return app(ContractTemplateService::class)->availableTemplates();
    }

    /**
     * Prefill the variable set whenever a lease is chosen.
     */
    public function updatedLeaseId(ContractTemplateService $templates): void
    {
        if (! $this->lease_id) {
            return;
        }

        $lease = $this->resolveLease($this->lease_id);

        if (! $lease) {
            $this->lease_id = null;

            return;
        }

        $this->v = array_merge($templates->defaultVariables(), $templates->buildVariablesFromLease($lease));
        $this->title = 'Bail commercial — '.($this->v['preneur_name'] ?? '');
    }

    protected function rules(): array
    {
        return [
            'type'              => 'required|string',
            'lease_id'          => 'nullable|exists:leases,id',
            'title'             => 'nullable|string|max:200',
            'v.bailleur_name'   => 'required|string|max:200',
            'v.bailleur_email'  => 'nullable|email|max:200',
            'v.preneur_name'    => 'required|string|max:200',
            'v.preneur_email'   => 'nullable|email|max:200',
            'v.premises_designation' => 'required|string|max:500',
            'v.premises_address'     => 'nullable|string|max:500',
            'v.destination'     => 'nullable|string|max:300',
            'v.duration_years'  => 'nullable|integer|min:1|max:99',
            'v.start_date'      => 'nullable|date',
            'v.end_date'        => 'nullable|date|after_or_equal:v.start_date',
            'v.monthly_rent'    => 'nullable|numeric|min:0|max:99999999',
            'v.annual_rent'     => 'nullable|numeric|min:0|max:999999999',
            'v.deposit'         => 'nullable|numeric|min:0|max:99999999',
            'v.charges'         => 'nullable|string|max:500',
            'v.special_conditions' => 'nullable|string|max:3000',
            'v.city_signed'     => 'nullable|string|max:120',
        ];
    }

    public function save(): void
    {
        $this->authorize('create', Contract::class);

        $validated = $this->validate();

        $variables = $this->v;
        if (filled($this->title)) {
            $variables['title'] = $this->title;
        }

        $service = app(ContractService::class);

        if ($this->lease_id) {
            $lease = $this->resolveLease($this->lease_id);
            abort_if(! $lease, 403);

            $contract = $service->createFromLease($lease, $variables, auth()->user(), $this->type);
        } else {
            $contract = $service->create(
                ['landlord_id' => auth()->id()],
                $variables,
                auth()->user(),
                $this->type,
            );
        }

        Flux::toast(__('Contract generated. Review it, then send for signature.'), 'success');

        $this->redirect(route('contracts.show', $contract), navigate: true);
    }

    /**
     * Load a lease, enforcing landlord ownership.
     */
    protected function resolveLease(int $id): ?Lease
    {
        $query = Lease::query()->whereKey($id);

        if (auth()->user()->hasRole('landlord')) {
            $query->forLandlord(auth()->id());
        }

        return $query->first();
    }

    public function render()
    {
        return view('livewire.contracts.create')
            ->layout('layouts.app')
            ->title(__('Generate Contract'));
    }
}
