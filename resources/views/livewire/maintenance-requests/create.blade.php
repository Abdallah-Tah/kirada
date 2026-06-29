<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('New Maintenance Request') }}</flux:heading>
        <flux:subheading>{{ __('Report a maintenance issue') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="mt-6 grid gap-6">
        <div class="kirada-form-card grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Issue Details') }}</h3>

            <div>
                <flux:label>{{ __('Title') }}</flux:label>
                <flux:input wire:model="title" type="text" required class="mt-1" :placeholder="__('Example: Kitchen sink is leaking')" />
                <flux:error name="title" />
            </div>

            <div>
                <flux:label>{{ __('Description') }}</flux:label>
                <flux:textarea wire:model="description" rows="4" required class="mt-1" :placeholder="__('Describe what happened, when it started, and anything the team should know.')" />
                <flux:error name="description" />
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <flux:label>{{ __('Issue Category') }}</flux:label>
                    <flux:select wire:model="category" required class="mt-1">
                        @foreach ($this->categoryOptions() as $value => $label)
                            <option value="{{ $value }}">{{ __($label) }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="category" />
                </div>

                <div>
                    <flux:label>{{ __('Room / Location') }}</flux:label>
                    <flux:input wire:model="location" type="text" class="mt-1" :placeholder="__('Kitchen, bathroom, parking...')" />
                    <flux:error name="location" />
                </div>

                <div>
                    <flux:label>{{ __('Priority') }}</flux:label>
                    <flux:select wire:model.live="priority" class="mt-1">
                        <option value="low">{{ __('Minor issue') }}</option>
                        <option value="medium">{{ __('Needs repair soon') }}</option>
                        <option value="high">{{ __('Important') }}</option>
                        <option value="urgent">{{ __('Emergency') }}</option>
                    </flux:select>
                    <flux:error name="priority" />
                </div>
            </div>

            @if ($priority === 'urgent')
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    {{ __('If this is dangerous or actively damaging the property, contact your landlord or emergency services immediately after submitting this request.') }}
                </div>
            @endif

            <div class="grid gap-4 sm:grid-cols-2">
                @if (auth()->user()->hasRole('tenant') && $property_id)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <span class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Property') }}</span>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $this->properties->firstWhere('id', $property_id)?->name ?? __('Assigned property') }}</p>
                    </div>
                @else
                    <div>
                        <flux:label>{{ __('Property') }}</flux:label>
                        <flux:select wire:model.live="property_id" required class="mt-1">
                            <option value="">{{ __('Select...') }}</option>
                            @foreach ($this->properties as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="property_id" />
                    </div>
                @endif

                @if (auth()->user()->hasRole('tenant') && $unit_id)
                    <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <span class="text-xs font-medium uppercase tracking-wide text-slate-400">{{ __('Unit') }}</span>
                        <p class="mt-1 text-sm font-semibold text-slate-800">{{ $this->units->firstWhere('id', $unit_id)?->unit_number ?? __('Assigned unit') }}</p>
                    </div>
                @else
                    <div>
                        <flux:label>{{ __('Unit') }}</flux:label>
                        <flux:select wire:model.live="unit_id" class="mt-1">
                            <option value="">{{ __('Optional') }}</option>
                            @foreach ($this->units as $u)
                                <option value="{{ $u->id }}">{{ $u->unit_number }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="unit_id" />
                    </div>
                @endif
            </div>

            @unless (auth()->user()->hasRole('tenant'))
                <div>
                    <flux:label>{{ __('Tenant') }}</flux:label>
                    <flux:select wire:model="tenant_id" class="mt-1">
                        <option value="">{{ __('Optional') }}</option>
                        @foreach ($this->tenants as $t)
                            <option value="{{ $t->id }}">{{ $t->first_name }} {{ $t->last_name }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="tenant_id" />
                </div>
            @endunless

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-white p-4">
                    <flux:checkbox wire:model="permission_to_enter" :label="__('Permission to enter if no one is home')" />
                    <flux:error name="permission_to_enter" />
                </div>

                <div>
                    <flux:label>{{ __('Preferred Access Window') }}</flux:label>
                    <flux:input wire:model="preferred_access_window" type="text" class="mt-1" :placeholder="__('Weekdays after 4 PM, call first...')" />
                    <flux:error name="preferred_access_window" />
                </div>
            </div>

            <div>
                <flux:label>{{ __('Photos') }}</flux:label>
                <input type="file" wire:model="photos" accept="image/*" multiple
                    class="mt-1 block w-full text-sm text-zinc-500 file:mr-4 file:rounded-md file:border-0 file:bg-zinc-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-zinc-200 dark:file:bg-zinc-700 dark:file:text-white" />
                <flux:error name="photos" />
                <flux:error name="photos.*" />
                <p class="mt-1 text-xs text-zinc-400">{{ __('Upload up to 6 photos, 5MB each. Clear photos help resolve issues faster.') }}</p>

                @if ($photos)
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        @foreach ($photos as $photo)
                            <img src="{{ $photo->temporaryUrl() }}" alt="{{ __('Selected maintenance photo') }}" class="h-28 w-full rounded-xl object-cover ring-1 ring-slate-200">
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <flux:button :href="route('maintenance-requests.index')" wire:navigate variant="ghost">
                {{ __('Cancel') }}
            </flux:button>
            <flux:button type="submit" variant="primary" icon="check">
                {{ __('Submit Request') }}
            </flux:button>
        </div>
    </form>
</div>
