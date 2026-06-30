<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('Edit Tenant') }}</flux:heading>
        <flux:subheading>{{ $tenant->full_name }}</flux:subheading>
    </div>

    <form wire:submit="save" class="mt-6 grid gap-6">
        {{-- Tenant Information --}}
        <div class="kirada-form-card grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Tenant Information') }}</h3>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('First Name') }}</flux:label>
                    <flux:input wire:model="first_name" type="text" required class="mt-1" />
                    <flux:error name="first_name" />
                </div>

                <div>
                    <flux:label>{{ __('Last Name') }}</flux:label>
                    <flux:input wire:model="last_name" type="text" required class="mt-1" />
                    <flux:error name="last_name" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Phone') }}</flux:label>
                    <flux:input wire:model="phone" type="tel" required class="mt-1" />
                    <flux:error name="phone" />
                </div>

                <div>
                    <flux:label>{{ __('Email') }}</flux:label>
                    <flux:input wire:model="email" type="email" class="mt-1" />
                    <flux:error name="email" />
                </div>
            </div>
        </div>

        {{-- ID Document --}}
        <div class="kirada-form-card grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Identity Document') }}</h3>
            <p class="text-sm text-zinc-500">{{ __('Upload a copy of the tenant\'s ID, passport, or driver\'s license for your records.') }}</p>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('ID Type') }}</flux:label>
                    <flux:select wire:model="id_type" class="mt-1">
                        <option value="">{{ __('Select type...') }}</option>
                        <option value="national_id">{{ __('National ID') }}</option>
                        <option value="passport">{{ __('Passport') }}</option>
                        <option value="driver_license">{{ __('Driver License') }}</option>
                        <option value="other">{{ __('Other') }}</option>
                    </flux:select>
                    <flux:error name="id_type" />
                </div>

                <div>
                    <flux:label>{{ __('ID Document Number') }}</flux:label>
                    <flux:input wire:model="id_document_number" type="text" class="mt-1" placeholder="{{ __('e.g. AB1234567') }}" />
                    <flux:error name="id_document_number" />
                </div>
            </div>

            <div>
                <flux:label>{{ __('National ID') }}</flux:label>
                <flux:input wire:model="national_id" type="text" class="mt-1" />
                <flux:error name="national_id" />
            </div>

            {{-- Existing document display --}}
            @if ($tenant->id_document_path && !$id_document)
                <div class="flex items-center gap-3 rounded-lg bg-zinc-50 dark:bg-zinc-800 p-3">
                    @if (str_ends_with(strtolower($tenant->id_document_path), '.pdf'))
                        <flux:icon.document class="h-8 w-8 text-zinc-400" />
                    @else
                        <img src="{{ asset('storage/' . $tenant->id_document_path) }}" alt="{{ __('ID document') }}" class="h-16 w-16 rounded-lg object-cover" />
                    @endif
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $tenant->id_document_original_filename ?? __('ID Document') }}</p>
                        <p class="text-xs text-zinc-500">{{ __('Uploaded') }}</p>
                    </div>
                    <a href="{{ asset('storage/' . $tenant->id_document_path) }}" target="_blank" class="text-sm text-kirada-ocean hover:underline">
                        {{ __('View') }}
                    </a>
                    <flux:button type="button" wire:click="removeIdDocument" wire:confirm="{{ __('Remove this document?') }}" variant="ghost" size="sm" icon="trash" />
                </div>
            @endif

            {{-- Upload new document --}}
            <div>
                <flux:label>{{ __('Upload New Document') }}</flux:label>
                <div class="mt-1">
                    <flux:input type="file" wire:model="id_document" accept="image/*,.pdf" class="hidden" id="id_document_upload" />
                    <flux:button type="button" onclick="document.getElementById('id_document_upload').click()" variant="outline" icon="document-plus">
                        {{ __('Choose file...') }}
                    </flux:button>

                    @if ($id_document)
                        <div class="mt-3 flex items-center gap-3 rounded-lg bg-zinc-50 dark:bg-zinc-800 p-3">
                            @if (str_starts_with($id_document->getMimeType(), 'image/'))
                                <img src="{{ $id_document->temporaryUrl() }}" alt="{{ __('ID preview') }}" class="h-16 w-16 rounded-lg object-cover" />
                            @else
                                <flux:icon.document class="h-8 w-8 text-zinc-400" />
                            @endif
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">{{ $id_document->getClientOriginalName() }}</p>
                                <p class="text-xs text-zinc-500">{{ number_format($id_document->getSize() / 1024, 1) }} KB</p>
                            </div>
                            <flux:button type="button" wire:click="$set('id_document', null)" variant="ghost" size="sm" icon="x-mark" />
                        </div>
                    @endif
                </div>
                <flux:error name="id_document" />
                <p class="mt-1 text-xs text-zinc-400">{{ __('Accepted: JPG, PNG, WebP, PDF. Max 10MB.') }}</p>
            </div>
        </div>

        {{-- Address --}}
        <div class="kirada-form-card grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Address') }}</h3>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Address') }}</flux:label>
                    <flux:input
                        wire:model="address"
                        type="text"
                        autocomplete="street-address"
                        data-google-address
                        data-google-address-method="applyGoogleAddress"
                        data-google-address-next="[wire\\:model='city'] input, [wire\\:model='city']"
                        class="mt-1"
                    />
                    <flux:error name="address" />
                </div>

                <div>
                    <flux:label>{{ __('City') }}</flux:label>
                    <flux:input wire:model="city" type="text" class="mt-1" />
                    <flux:error name="city" />
                </div>
            </div>
        </div>

        {{-- Status & Notes --}}
        <div class="kirada-form-card grid gap-4">
            <div>
                <flux:label>{{ __('Status') }}</flux:label>
                <flux:select wire:model="status" class="mt-1">
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                </flux:select>
                <flux:error name="status" />
            </div>

            <div>
                <flux:label>{{ __('Notes') }}</flux:label>
                <flux:textarea wire:model="notes" rows="3" class="mt-1" />
                <flux:error name="notes" />
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('tenants.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Save Changes') }}
            </flux:button>
        </div>
    </form>
</div>