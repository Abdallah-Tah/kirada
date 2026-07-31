<?php

use App\Models\LandlordNotificationSetting;
use App\Models\RentInvoice;
use App\Services\Bwa\BwaMessagingApi;
use App\Services\InvoicePdfFactory;
use App\Services\NotificationChannelResolver;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Title('Notification settings')] class extends Component {
    /** @var array<int, string> */
    public array $invoiceChannels = ['email'];

    /** @var array<int, string> */
    public array $reminderChannels = ['email'];

    public bool $autoSendInvoices = true;

    public bool $attachPdfToEmail = true;

    public bool $whatsAppConfigured = false;

    public function mount(BwaMessagingApi $whatsApp): void
    {
        abort_unless(Auth::user()?->can('notifications.manage'), 403);

        $account = Auth::user()->landlordAccount();
        abort_unless($account, 403);

        $setting = $account->notificationSetting;
        $this->invoiceChannels = $setting?->invoice_channels ?? ['email'];
        $this->reminderChannels = $setting?->reminder_channels ?? ['email'];
        $this->autoSendInvoices = $setting?->auto_send_invoices ?? true;
        $this->attachPdfToEmail = $setting?->attach_pdf_to_email ?? true;
        $this->whatsAppConfigured = $whatsApp->isConfigured();
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->can('notifications.manage'), 403);

        $validated = $this->validate([
            'invoiceChannels' => ['required', 'array', 'min:1'],
            'invoiceChannels.*' => ['required', Rule::in(LandlordNotificationSetting::CHANNELS)],
            'reminderChannels' => ['required', 'array', 'min:1'],
            'reminderChannels.*' => ['required', Rule::in(LandlordNotificationSetting::CHANNELS)],
            'autoSendInvoices' => ['boolean'],
            'attachPdfToEmail' => ['boolean'],
        ]);

        if (! $this->whatsAppConfigured
            && (in_array('whatsapp', $validated['invoiceChannels'], true)
                || in_array('whatsapp', $validated['reminderChannels'], true))) {
            $this->addError('invoiceChannels', __('Configure the BWA Messaging API before enabling WhatsApp.'));

            return;
        }

        $account = Auth::user()->landlordAccount();
        abort_unless($account, 403);

        LandlordNotificationSetting::updateOrCreate(
            ['landlord_id' => $account->id],
            [
                'invoice_channels' => array_values(array_unique($validated['invoiceChannels'])),
                'reminder_channels' => array_values(array_unique($validated['reminderChannels'])),
                'auto_send_invoices' => $validated['autoSendInvoices'],
                'attach_pdf_to_email' => $validated['attachPdfToEmail'],
            ],
        );

        Flux::toast(variant: 'success', text: __('Notification settings updated.'));
    }

    public function sendWhatsAppTest(
        BwaMessagingApi $whatsApp,
        InvoicePdfFactory $pdfFactory,
        NotificationChannelResolver $resolver,
    ): void {
        abort_unless(Auth::user()?->can('notifications.manage'), 403);

        $account = Auth::user()->landlordAccount();
        $invoice = RentInvoice::forLandlord($account->id)->latest()->first();

        if (! $invoice) {
            $this->addError('invoiceChannels', __('Create an invoice before sending a WhatsApp test.'));

            return;
        }

        try {
            $recipient = $resolver->whatsAppRecipient($invoice);

            if (! $recipient) {
                $this->addError('invoiceChannels', __('The invoice tenant must opt in to WhatsApp before testing.'));

                return;
            }

            $whatsApp->sendDocumentTemplate(
                $recipient,
                (string) config('services.bwa.invoice_template'),
                (string) config('services.bwa.template_language', 'fr'),
                $pdfFactory->make($invoice),
                [
                    $invoice->invoice_number,
                    $invoice->tenant?->full_name ?? '—',
                    $invoice->formatted_total_due,
                    $invoice->due_date?->format('d/m/Y') ?? '—',
                    $invoice->payment_reference ?? $invoice->invoice_number,
                ],
                $invoice->invoice_number.'.pdf',
                hash('sha256', implode('|', [
                    'settings_connection_test',
                    $account->id,
                    $invoice->id,
                    now()->format('YmdH'),
                ])),
            );
        } catch (\Throwable $exception) {
            report($exception);
            $this->addError('invoiceChannels', __('BWA did not accept the test invoice. Check the application registration, template, recipient, and logs.'));

            return;
        }

        Flux::toast(variant: 'success', text: __('The WhatsApp test invoice was accepted by BWA.'));
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Notification settings') }}</flux:heading>

    <x-pages::settings.layout
        :heading="__('Invoice notifications')"
        :subheading="__('Choose how Kirada sends invoices and rent reminders to tenants.')"
        content-class="max-w-4xl"
    >
        <form wire:submit="save" class="my-6 space-y-6">
            <div class="rounded-xl border border-sky-200 bg-sky-50 p-4 text-sm text-sky-950 dark:border-sky-800 dark:bg-sky-950/40 dark:text-sky-100">
                <div class="font-semibold">{{ __('Secure WhatsApp delivery through BWA') }}</div>
                <p class="mt-1">
                    {{ __('Kirada signs each request to BWA and never stores or receives Meta credentials.') }}
                </p>
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div class="kirada-form-card space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <flux:heading size="lg">{{ __('New invoices') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('Channels used when a generated invoice is sent.') }}</flux:text>
                        </div>
                        <flux:icon.document-text class="size-6 text-sky-600" />
                    </div>

                    <flux:checkbox wire:model="invoiceChannels" value="email" :label="__('Email with PDF')" />
                    <flux:checkbox
                        wire:model="invoiceChannels"
                        value="whatsapp"
                        :disabled="! $whatsAppConfigured"
                        :label="__('WhatsApp template with PDF')"
                    />
                    <flux:error name="invoiceChannels" />
                </div>

                <div class="kirada-form-card space-y-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <flux:heading size="lg">{{ __('Rent reminders') }}</flux:heading>
                            <flux:text class="mt-1">{{ __('Channels used for the reminder schedule on each lease.') }}</flux:text>
                        </div>
                        <flux:icon.bell-alert class="size-6 text-cyan-600" />
                    </div>

                    <flux:checkbox wire:model="reminderChannels" value="email" :label="__('Email with PDF')" />
                    <flux:checkbox
                        wire:model="reminderChannels"
                        value="whatsapp"
                        :disabled="! $whatsAppConfigured"
                        :label="__('WhatsApp template with PDF')"
                    />
                    <flux:error name="reminderChannels" />
                </div>
            </div>

            <div class="kirada-form-card space-y-4">
                <flux:checkbox
                    wire:model="autoSendInvoices"
                    :label="__('Automatically send system-generated invoices')"
                    :description="__('Manual invoices remain drafts until an authorized user confirms Send invoice.')"
                />
                <flux:checkbox
                    wire:model="attachPdfToEmail"
                    :label="__('Attach the premium invoice PDF to email')"
                />
            </div>

            <div class="flex flex-wrap items-center justify-between gap-3">
                @if ($whatsAppConfigured)
                    <flux:button
                        type="button"
                        variant="outline"
                        icon="paper-airplane"
                        wire:click="sendWhatsAppTest"
                        data-confirm="{{ __('Submit the latest invoice as a BWA WhatsApp test request?') }}"
                        data-confirm-title="{{ __('Send WhatsApp test') }}"
                        data-confirm-button="{{ __('Send test') }}"
                    >
                        {{ __('Send WhatsApp test') }}
                    </flux:button>
                @else
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                        {{ $whatsAppConfigured
                            ? __('WhatsApp is connected through BWA.')
                            : __('WhatsApp is unavailable until the BWA Messaging API is configured.') }}
                    </div>
                @endif

                <flux:button type="submit" variant="primary" icon="check">
                    {{ __('Save notification settings') }}
                </flux:button>
            </div>
        </form>
    </x-pages::settings.layout>
</section>
