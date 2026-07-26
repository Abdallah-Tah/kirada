<div>
    <div class="kirada-page-header kirada-reveal">
        <flux:heading size="xl">{{ __('My Provider Profile') }}</flux:heading>
        <flux:subheading>{{ __('This is what landlords see when they search the maintenance directory') }}</flux:subheading>
    </div>

    {{-- Status strip: completeness + publish/verified state --}}
    @php $profile = $this->profile; @endphp
    <div class="kirada-status-strip mt-6">
        <div class="kirada-status-strip-main">
            <div class="flex flex-wrap items-center gap-2">
                @if($profile?->is_published)
                    <flux:badge color="green" size="sm" icon="check-circle">{{ __('Listed') }}</flux:badge>
                @else
                    <flux:badge color="zinc" size="sm" icon="eye-slash">{{ __('Not listed') }}</flux:badge>
                @endif

                @if($profile?->isVerified())
                    <flux:badge color="blue" size="sm" icon="shield-check">{{ __('Verified') }}</flux:badge>
                @else
                    <flux:badge color="zinc" size="sm">{{ __('Awaiting verification') }}</flux:badge>
                @endif
            </div>

            <p class="mt-2 text-sm text-slate-500">
                @if($profile?->is_published)
                    {{ __('Landlords in your service areas can find and contact you.') }}
                @else
                    {{ __('Turn on "List me in the directory" below so landlords can find you.') }}
                @endif
            </p>
        </div>

        @if($profile)
            <div class="kirada-status-strip-meter">
                <div class="flex items-center justify-between text-xs font-medium text-slate-500">
                    <span>{{ __('Profile completeness') }}</span>
                    <span>{{ $profile->completeness() }}%</span>
                </div>
                <div class="kirada-meter mt-1.5" role="progressbar"
                     aria-valuenow="{{ $profile->completeness() }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="kirada-meter-fill" style="width: {{ $profile->completeness() }}%"></div>
                </div>
            </div>
        @endif
    </div>

    <form wire:submit="save" class="mt-6 grid gap-6">
        {{-- ── Business identity ── --}}
        <div class="kirada-form-card grid gap-4">
            <h3 class="font-semibold text-zinc-900">{{ __('Your business') }}</h3>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Business name') }}</flux:label>
                    <flux:input wire:model="business_name" type="text" required class="mt-1" />
                    <flux:error name="business_name" />
                    @if($profile?->isVerified())
                        <flux:text size="sm" class="mt-1 text-amber-600">
                            {{ __('Changing this clears your Verified badge until an admin reviews it again.') }}
                        </flux:text>
                    @endif
                </div>

                <div>
                    <flux:label>{{ __('Years of experience') }}</flux:label>
                    <flux:input wire:model="years_experience" type="number" min="0" max="80" class="mt-1" />
                    <flux:error name="years_experience" />
                </div>
            </div>

            <div>
                <flux:label>{{ __('Professional headline') }}</flux:label>
                <flux:input wire:model="headline" class="mt-1" :placeholder="__('e.g. Licensed electrician for residential and commercial work')" />
                <flux:error name="headline" />
            </div>

            <div>
                <flux:label>{{ __('About your work') }}</flux:label>
                <flux:textarea wire:model="bio" rows="4" class="mt-1"
                    :placeholder="__('Describe your experience, specialities, and how quickly you usually respond.')" />
                <flux:error name="bio" />
            </div>
        </div>

        <div class="kirada-form-card grid gap-4">
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Availability') }}</flux:label>
                    <flux:select wire:model="availability_status" class="mt-1">
                        <option value="available">{{ __('Available for work') }}</option>
                        <option value="busy">{{ __('Limited availability') }}</option>
                        <option value="unavailable">{{ __('Not accepting work') }}</option>
                    </flux:select>
                </div>
                <div>
                    <flux:label>{{ __('Website') }}</flux:label>
                    <flux:input wire:model="website" type="url" class="mt-1" placeholder="https://example.com" />
                    <flux:error name="website" />
                </div>
            </div>
            <div>
                <flux:label>{{ __('Working languages') }}</flux:label>
                <div class="mt-1 flex gap-2">
                    <flux:input wire:model="newLanguage" wire:keydown.enter.prevent="addLanguage" :placeholder="__('e.g. Somali, French, Arabic')" />
                    <flux:button type="button" wire:click="addLanguage" icon="plus">{{ __('Add') }}</flux:button>
                </div>
                <div class="mt-2 flex flex-wrap gap-2">
                    @foreach($languages as $index => $language)
                        <span class="kirada-removable-chip">{{ $language }} <button type="button" wire:click="removeLanguage({{ $index }})"><flux:icon.x-mark class="size-3.5" /></button></span>
                    @endforeach
                </div>
                <flux:error name="languages" />
            </div>
        </div>

        {{-- ── Trades ── --}}
        <div class="kirada-form-card grid gap-4">
            <div>
                <h3 class="font-semibold text-zinc-900">{{ __('Trades you offer') }}</h3>
                <flux:text size="sm" class="text-slate-500">{{ __('Landlords filter the directory by these.') }}</flux:text>
            </div>

            <div class="kirada-chip-grid">
                @foreach($this->availableTrades as $trade)
                    <label class="kirada-chip-option">
                        <input type="checkbox" wire:model="trades" value="{{ $trade }}">
                        <span class="kirada-chip-option-body">{{ __('trades.'.$trade) }}</span>
                    </label>
                @endforeach
            </div>
            <flux:error name="trades" />
        </div>

        {{-- ── Service areas ── --}}
        <div class="kirada-form-card grid gap-4">
            <div>
                <h3 class="font-semibold text-zinc-900">{{ __('Areas you serve') }}</h3>
                <flux:text size="sm" class="text-slate-500">{{ __('Add each city or district you travel to.') }}</flux:text>
            </div>

            <div class="flex flex-col gap-2 sm:flex-row">
                <flux:input
                    wire:model="newArea"
                    wire:keydown.enter.prevent="addArea"
                    type="text"
                    class="flex-1"
                    :placeholder="__('e.g. Balbala, Djibouti Ville')"
                />
                <flux:button type="button" wire:click="addArea" icon="plus" class="sm:w-auto">
                    {{ __('Add') }}
                </flux:button>
            </div>

            @if($service_areas)
                <div class="flex flex-wrap gap-2">
                    @foreach($service_areas as $index => $area)
                        <span class="kirada-removable-chip">
                            {{ $area }}
                            <button type="button" wire:click="removeArea({{ $index }})"
                                    aria-label="{{ __('Remove :area', ['area' => $area]) }}">
                                <flux:icon.x-mark class="size-3.5" />
                            </button>
                        </span>
                    @endforeach
                </div>
            @endif
            <flux:error name="service_areas" />
        </div>

        {{-- ── Rates & contact ── --}}
        <div class="kirada-form-card grid gap-4">
            <div>
                <h3 class="font-semibold text-zinc-900">{{ __('Rates and contact') }}</h3>
                <flux:text size="sm" class="text-slate-500">{{ __('Optional, but profiles with rates get contacted more.') }}</flux:text>
            </div>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <flux:label>{{ __('Currency') }}</flux:label>
                    <flux:select wire:model="currency_id" class="mt-1">
                        <option value="">{{ __('Select') }}</option>
                        @foreach($this->currencies as $currency)
                            <option value="{{ $currency->id }}">{{ $currency->code }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="currency_id" />
                </div>

                <div>
                    <flux:label>{{ __('Hourly rate') }}</flux:label>
                    <flux:input wire:model="hourly_rate" type="number" min="0" class="mt-1" />
                    <flux:error name="hourly_rate" />
                </div>

                <div>
                    <flux:label>{{ __('Call-out fee') }}</flux:label>
                    <flux:input wire:model="callout_fee" type="number" min="0" class="mt-1" />
                    <flux:error name="callout_fee" />
                </div>
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <flux:label>{{ __('Phone') }}</flux:label>
                    <flux:input wire:model="phone" type="tel" inputmode="tel" class="mt-1" placeholder="+253 77 00 00 00" />
                    <flux:error name="phone" />
                </div>

                <div>
                    <flux:label>{{ __('WhatsApp') }}</flux:label>
                    <flux:input wire:model="whatsapp" type="tel" inputmode="tel" class="mt-1" placeholder="+253 77 00 00 00" />
                    <flux:error name="whatsapp" />
                </div>
            </div>
        </div>

        {{-- ── Visibility ── --}}
        <div class="kirada-form-card">
            <flux:switch
                wire:model="is_published"
                :label="__('List me in the directory')"
                :description="__('Landlords can find your profile and invite you to their maintenance team. Contact details are shown only to signed-in landlords.')"
            />
        </div>

        <div class="kirada-form-actions">
            <flux:button type="submit" variant="primary" icon="check">{{ __('Save profile') }}</flux:button>
            <flux:button :href="route('maintenance-network.inbox')" wire:navigate variant="ghost">
                {{ __('Work invitations') }}
            </flux:button>
        </div>
    </form>
</div>
