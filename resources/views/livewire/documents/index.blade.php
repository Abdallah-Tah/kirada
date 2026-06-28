<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('messages.Documents') }}</flux:heading>
    <flux:subheading>{{ __('Lease agreements, receipts, payment proofs, and more') }}</flux:subheading>
    </div>

    <div class="kirada-toolbar mt-6">
        <flux:input
            wire:model.live="search"
            type="search"
            :placeholder="__('Search by title, filename, tenant...')"
            class="w-72"
            icon="magnifying-glass"
        />

        <flux:select wire:model.live="filterType" :placeholder="__('All types')" class="w-44">
            <option value="">{{ __('All') }}</option>
            <option value="lease_agreement">{{ __('Lease Agreement') }}</option>
            <option value="payment_receipt">{{ __('Payment Receipt') }}</option>
            <option value="payment_proof">{{ __('Payment Proof') }}</option>
            <option value="id_document">{{ __('ID Document') }}</option>
            <option value="other">{{ __('Other') }}</option>
        </flux:select>

        <flux:spacer />

        @can('create', \App\Models\Document::class)
            <flux:button :href="route('documents.create')" wire:navigate variant="primary" icon="arrow-up-tray">
                {{ __('Upload Document') }}
            </flux:button>
        @endcan
    </div>

    <div class="kirada-table-card mt-4">
        <table class="w-full text-left text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-900">
                <tr>
                    <th class="px-4 py-3 font-medium">{{ __('Title') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Type') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Tenant') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Size') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Visibility') }}</th>
                    <th class="px-4 py-3 font-medium">{{ __('Uploaded') }}</th>
                    <th class="px-4 py-3 font-medium text-right">{{ __('Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse ($this->documents as $document)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800">
                        <td class="px-4 py-3 font-medium">{{ $document->title }}</td>
                        <td class="px-4 py-3 text-zinc-500">{{ __($document->typeLabel) }}</td>
                        <td class="px-4 py-3 text-zinc-500">
                            {{ $document->tenant ? $document->tenant->first_name . ' ' . $document->tenant->last_name : '—' }}
                        </td>
                        <td class="px-4 py-3 text-zinc-500">{{ $document->formattedSize }}</td>
                        <td class="px-4 py-3">
                            @if($document->visibility === 'landlord_only')
                                <flux:badge color="zinc" size="sm">{{ __('Landlord Only') }}</flux:badge>
                            @elseif($document->visibility === 'tenant_visible')
                                <flux:badge color="blue" size="sm">{{ __('Tenant Visible') }}</flux:badge>
                            @else
                                <flux:badge color="red" size="sm">{{ __('Admin Only') }}</flux:badge>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-zinc-500">{{ $document->created_at?->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <flux:dropdown align="end">
                                <flux:button icon="ellipsis-horizontal" variant="ghost" size="sm" />
                                <flux:menu>
                                    <flux:menu.item
                                        :href="route('documents.download', $document)"
                                        icon="arrow-down-tray"
                                    >
                                        {{ __('Download') }}
                                    </flux:menu.item>
                                    @can('delete', $document)
                                        <flux:menu.separator />
                                        <flux:menu.item
                                            wire:click="deleteDocument({{ $document->id }})"
                                            data-confirm="{{ __('Delete this document? The file will be permanently removed.') }}"
                                            icon="trash"
                                            variant="danger"
                                        >
                                            {{ __('Delete') }}
                                        </flux:menu.item>
                                    @endcan
                                </flux:menu>
                            </flux:dropdown>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-zinc-500">
                            {{ __('No documents found.') }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $this->documents->links() }}
    </div>
</div>
