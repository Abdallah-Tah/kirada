<?php

use App\Models\Tenant;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Tenant notification settings')] class extends Component {
    public string $phone = '';

    public bool $whatsAppOptIn = false;

    public function mount(): void
    {
        abort_unless(Auth::user()?->isTenant(), 403);

        $tenant = $this->tenants()->firstOrFail();
        $this->phone = $tenant->phone ?? '';
        $this->whatsAppOptIn = $tenant->hasWhatsAppConsent();
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->isTenant(), 403);

        $validated = $this->validate([
            'phone' => ['required', 'regex:/^\+[1-9]\d{7,14}$/'],
            'whatsAppOptIn' => ['boolean'],
        ], [
            'phone.regex' => __('Use an international number such as +25377123456.'),
        ]);

        $this->tenants()->each(function (Tenant $tenant) use ($validated): void {
            $wasOptedIn = $tenant->hasWhatsAppConsent();

            $tenant->update([
                'phone' => $validated['phone'],
                'whatsapp_consented_at' => $validated['whatsAppOptIn']
                    ? ($tenant->whatsapp_consented_at ?? now())
                    : $tenant->whatsapp_consented_at,
                'whatsapp_consent_revoked_at' => $validated['whatsAppOptIn']
                    ? null
                    : ($wasOptedIn ? now() : $tenant->whatsapp_consent_revoked_at),
                'whatsapp_consent_source' => $validated['whatsAppOptIn']
                    ? 'tenant_settings'
                    : $tenant->whatsapp_consent_source,
            ]);
        });

        Flux::toast(variant: 'success', text: __('Notification preferences updated.'));
    }

    /** @return Collection<int, Tenant> */
    private function tenants(): Collection
    {
        return Tenant::where('user_id', Auth::id())->get();
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-pages::settings.layout
        :heading="__('Notification preferences')"
        :subheading="__('Choose whether Kirada may send rent invoices and reminders to your WhatsApp number.')"
        content-class="max-w-2xl"
    >
        <form wire:submit="save" class="my-6 space-y-5">
            <div class="kirada-form-card space-y-4">
                <flux:input
                    wire:model="phone"
                    type="tel"
                    inputmode="tel"
                    :label="__('WhatsApp number')"
                    placeholder="+25377123456"
                />
                <flux:error name="phone" />

                <flux:checkbox
                    wire:model="whatsAppOptIn"
                    :label="__('Receive Kirada invoices and rent reminders on WhatsApp')"
                    :description="__('You can withdraw this permission at any time. Email delivery remains controlled by your landlord.')"
                />
            </div>

            <div class="flex justify-end">
                <flux:button type="submit" variant="primary" icon="check">
                    {{ __('Save notification preferences') }}
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
