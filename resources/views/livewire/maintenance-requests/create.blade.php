<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('New Maintenance Request') }}</flux:heading>
    <flux:subheading>{{ __('Report a maintenance issue') }}</flux:subheading>
    </div>

    <form wire:submit="save" class="mt-6 grid gap-6">
        <div class="kirada-form-card grid gap-4">
            <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Details') }}</h3>

            <div>
                <flux:label>{{ __('Title') }}</flux:label>
                <flux:input wire:model="title" type="text" required class="mt-1" />
                <flux:error name="title" />
            </div>

            <div>
                <flux:label>{{ __('Description') }}</flux:label>
                <flux:textarea wire:model="description" rows="4" required class="mt-1" />
                <flux:error name="description" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
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
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
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

                <div>
                    <flux:label>{{ __('Priority') }}</flux:label>
                    <flux:select wire:model="priority" class="mt-1">
                        <option value="low">{{ __('Low') }}</option>
                        <option value="medium">{{ __('Medium') }}</option>
                        <option value="high">{{ __('High') }}</option>
                        <option value="urgent">{{ __('Urgent') }}</option>
                    </flux:select>
                    <flux:error name="priority" />
                </div>
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