<?php

use App\Models\LandlordPayoutAccount;
use App\Services\LandlordPayoutAccountService;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Payment accounts')] class extends Component {
    /** @var array<int, array<string, mixed>> */
    public array $accounts = [];

    public ?int $primaryIndex = null;

    public function mount(): void
    {
        abort_unless(Auth::user()?->isLandlord(), 403);

        $this->loadAccounts();
    }

    public function addAccount(): void
    {
        if (count($this->accounts) >= 20) {
            $this->addError('accounts', __('You can add up to 20 payment accounts.'));

            return;
        }

        $this->accounts[] = [
            'key' => (string) Str::uuid(),
            'id' => null,
            'label' => '',
            'method' => 'd_money',
            'account_number' => '',
            'account_name' => '',
            'instructions' => '',
            'is_active' => true,
        ];

        if ($this->primaryIndex === null) {
            $this->primaryIndex = array_key_last($this->accounts);
        }
    }

    public function removeAccount(int $index): void
    {
        if (! array_key_exists($index, $this->accounts)) {
            return;
        }

        unset($this->accounts[$index]);
        $this->accounts = array_values($this->accounts);

        if ($this->accounts === []) {
            $this->primaryIndex = null;
        } elseif ($this->primaryIndex === $index) {
            $this->primaryIndex = 0;
        } elseif ($this->primaryIndex !== null && $this->primaryIndex > $index) {
            $this->primaryIndex--;
        }
    }

    public function saveAccounts(LandlordPayoutAccountService $service): void
    {
        $validated = $this->validate([
            'accounts' => ['array', 'max:20'],
            'accounts.*.id' => ['nullable', 'integer'],
            'accounts.*.key' => ['required', 'string'],
            'accounts.*.label' => ['required', 'string', 'max:100'],
            'accounts.*.method' => ['required', Rule::in(array_keys(LandlordPayoutAccount::METHODS))],
            'accounts.*.account_number' => ['nullable', 'string', 'max:100'],
            'accounts.*.account_name' => ['nullable', 'string', 'max:100'],
            'accounts.*.instructions' => ['nullable', 'string', 'max:1000'],
            'accounts.*.is_active' => ['boolean'],
            'primaryIndex' => ['nullable', 'integer', 'min:0'],
        ]);

        try {
            $service->sync(Auth::user(), $validated['accounts'], $validated['primaryIndex']);
        } catch (\DomainException $exception) {
            $this->addError('accounts', __($exception->getMessage()));

            return;
        }

        $this->loadAccounts();
        Flux::toast(variant: 'success', text: __('Payment accounts updated.'));
    }

    private function loadAccounts(): void
    {
        $this->accounts = Auth::user()
            ->payoutAccounts()
            ->get()
            ->map(fn (LandlordPayoutAccount $account) => [
                'key' => 'account-'.$account->id,
                'id' => $account->id,
                'label' => $account->label,
                'method' => $account->method,
                'account_number' => $account->account_number ?? '',
                'account_name' => $account->account_name ?? '',
                'instructions' => $account->instructions ?? '',
                'is_active' => $account->is_active,
            ])
            ->values()
            ->all();

        $primary = Auth::user()->payoutAccounts()->get()->search(
            fn (LandlordPayoutAccount $account) => $account->is_primary,
        );

        $this->primaryIndex = $primary === false ? null : $primary;
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Payment accounts') }}</flux:heading>

    <x-pages::settings.layout
        :heading="__('Payment accounts')"
        :subheading="__('Add the D-Money, Waafi, bank, cash, or custom accounts tenants can pay.')"
        content-class="max-w-3xl"
    >
        <form wire:submit="saveAccounts" class="my-6 space-y-5">
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-900 dark:border-sky-900 dark:bg-sky-950/40 dark:text-sky-100">
                {{ __('You can add several payment options. Choose one primary account to show tenants first.') }}
            </div>

            <flux:error name="accounts" />

            @forelse ($this->accounts as $index => $account)
                <div wire:key="{{ $account['key'] }}" class="kirada-form-card space-y-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <flux:radio
                                wire:model.live="primaryIndex"
                                value="{{ $index }}"
                                :label="__('Primary')"
                            />
                            @if ($this->primaryIndex === $index)
                                <flux:badge color="sky" size="sm">{{ __('Shown first') }}</flux:badge>
                            @endif
                        </div>

                        <flux:button
                            type="button"
                            variant="ghost"
                            size="sm"
                            icon="trash"
                            wire:click="removeAccount({{ $index }})"
                            data-confirm="{{ __('Remove this payment account from the form? Save your changes to make the removal permanent.') }}"
                            data-confirm-title="{{ __('Remove payment account') }}"
                            data-confirm-button="{{ __('Remove') }}"
                            data-confirm-variant="danger"
                        >
                            {{ __('Remove') }}
                        </flux:button>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input
                            wire:model="accounts.{{ $index }}.label"
                            :label="__('Account label')"
                            :placeholder="__('e.g. Rent D-Money')"
                            required
                        />

                        <flux:select wire:model="accounts.{{ $index }}.method" :label="__('Payment method')">
                            @foreach (LandlordPayoutAccount::METHODS as $value => $label)
                                <flux:select.option value="{{ $value }}">{{ __($label) }}</flux:select.option>
                            @endforeach
                        </flux:select>

                        <flux:input
                            wire:model="accounts.{{ $index }}.account_number"
                            :label="__('Phone or account number')"
                            :placeholder="__('e.g. 77 12 34 56')"
                        />

                        <flux:input
                            wire:model="accounts.{{ $index }}.account_name"
                            :label="__('Account holder name')"
                            :placeholder="__('Name registered on the account')"
                        />
                    </div>

                    <flux:textarea
                        wire:model="accounts.{{ $index }}.instructions"
                        :label="__('Instructions')"
                        :placeholder="__('Optional notes shown to tenants')"
                        rows="2"
                    />

                    <flux:checkbox
                        wire:model="accounts.{{ $index }}.is_active"
                        :label="__('Available to tenants')"
                    />
                </div>
            @empty
                <div class="kirada-empty-state">
                    <flux:icon.credit-card class="mx-auto size-8 text-slate-400" />
                    <p class="mt-3 font-medium text-slate-700 dark:text-slate-200">{{ __('No payment accounts yet') }}</p>
                    <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ __('Add your first D-Money, Waafi, bank, cash, or custom option.') }}</p>
                </div>
            @endforelse

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:items-center sm:justify-between">
                <flux:button type="button" variant="outline" icon="plus" wire:click="addAccount">
                    {{ __('Add payment account') }}
                </flux:button>

                <flux:button type="submit" variant="primary" icon="check">
                    {{ __('Save payment accounts') }}
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
