<x-layouts::legal>
    <article class="prose prose-slate max-w-none">
        <header class="mb-8 not-prose">
            <h1 class="text-3xl font-bold text-slate-900">{{ __('How It Works') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ __('Last updated') }}: June 30, 2026</p>
        </header>

        <div class="space-y-10 text-slate-700 leading-relaxed">

        {{-- Intro --}}
        <section>
            <p class="mt-3 text-lg">{{ __('Kirada is a property management app that helps landlords, tenants, and maintenance teams stay organized — all in one place.') }}</p>
            <div class="mt-4 rounded-xl bg-kirada-ocean/5 border border-kirada-ocean/20 p-4">
                <p class="font-semibold text-slate-900">{{ __('Kirada does not handle money.') }}</p>
                <p class="mt-1 text-sm">{{ __('It tracks invoice status and payment records, but all actual payments happen outside the platform through whatever method you agree on.') }}</p>
            </div>
        </section>

        {{-- Landlord --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('For Landlords') }}</h2>
            <h3 class="mt-4 font-semibold text-slate-800">{{ __('Getting Started') }}</h3>
            <ol class="mt-2 list-decimal pl-6 space-y-2">
                <li>{{ __('Register as a landlord') }}</li>
                <li>{{ __('Add properties — create buildings, units, and track occupancy') }}</li>
                <li>{{ __('Invite tenants — send invitations; tenants create their own accounts') }}</li>
                <li>{{ __('Create leases — set terms, rent amounts, dates, and deposits') }}</li>
                <li>{{ __('Generate invoices — create rent invoices; tenants see them and record payments') }}</li>
                <li>{{ __('Manage maintenance — tenants submit requests; you assign them to maintenance workers') }}</li>
                <li>{{ __('Sign contracts — create digital contracts and send signing links to tenants') }}</li>
            </ol>
            <h3 class="mt-4 font-semibold text-slate-800">{{ __('Key Features') }}</h3>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('Property dashboard — vacancy rates, occupied units, rent status') }}</li>
                <li>{{ __('Tenant management — profiles, lease history, contact info') }}</li>
                <li>{{ __('Lease tracking — start/end dates, renewals, deposits') }}</li>
                <li>{{ __('Invoice system — generate, track, upload payment proof') }}</li>
                <li>{{ __('Maintenance workflow — request → assign → track → resolve') }}</li>
                <li>{{ __('Digital contracts — create, send, e-sign, archive PDF') }}</li>
                <li>{{ __('Messaging — communicate with tenants in the app') }}</li>
                <li>{{ __('AI assistant — ask property management questions') }}</li>
            </ul>
        </section>

        {{-- Tenant --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('For Tenants') }}</h2>
            <h3 class="mt-4 font-semibold text-slate-800">{{ __('Getting Started') }}</h3>
            <ol class="mt-2 list-decimal pl-6 space-y-2">
                <li>{{ __('Accept invitation — your landlord sends an email invitation') }}</li>
                <li>{{ __('Create account — set your password and complete your profile') }}</li>
                <li>{{ __('View your lease — see terms, rent amount, and dates') }}</li>
                <li>{{ __('Track invoices — view rent invoices and mark payments') }}</li>
                <li>{{ __('Submit maintenance requests — report issues with photos') }}</li>
                <li>{{ __('Sign contracts — review and e-sign contracts from your landlord') }}</li>
                <li>{{ __('Message your landlord — communicate directly through the app') }}</li>
            </ol>
            <h3 class="mt-4 font-semibold text-slate-800">{{ __('Key Features') }}</h3>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('Lease dashboard — terms, rent status, important dates') }}</li>
                <li>{{ __('Invoice view — see what\'s due, what\'s paid, download records') }}</li>
                <li>{{ __('Maintenance requests — submit issues, attach photos, track progress') }}</li>
                <li>{{ __('Contract signing — review and sign digitally') }}</li>
                <li>{{ __('Messaging — talk to your landlord without sharing personal contact info') }}</li>
                <li>{{ __('Multi-language — switch interface language anytime') }}</li>
            </ul>
        </section>

        {{-- Maintenance --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('For Maintenance Workers') }}</h2>
            <ol class="mt-2 list-decimal pl-6 space-y-2">
                <li>{{ __('Receive assignment — landlord assigns you a maintenance request') }}</li>
                <li>{{ __('Review details — see the issue description, photos, and unit location') }}</li>
                <li>{{ __('Update status — mark progress as you work') }}</li>
                <li>{{ __('Complete and report — mark resolved and add notes about the fix') }}</li>
            </ol>
        </section>

        {{-- Rent tracking flow --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('How Rent Tracking Works') }}</h2>
            <div class="mt-4 rounded-xl bg-slate-50 border border-slate-200 p-6 not-prose">
                <div class="flex flex-col gap-3 text-sm font-medium text-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-kirada-ocean text-white text-xs">1</span>
                        {{ __('Landlord creates invoice') }}
                    </div>
                    <div class="ml-3.5 h-4 border-l border-slate-300"></div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-kirada-ocean text-white text-xs">2</span>
                        {{ __('Tenant sees invoice') }}
                    </div>
                    <div class="ml-3.5 h-4 border-l border-slate-300"></div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-kirada-ocean text-white text-xs">3</span>
                        {{ __('Tenant pays outside Kirada (bank, cash, check)') }}
                    </div>
                    <div class="ml-3.5 h-4 border-l border-slate-300"></div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-kirada-green text-white text-xs">4</span>
                        {{ __('Payment recorded, status marked "Paid"') }}
                    </div>
                </div>
            </div>
            <p class="mt-3 text-sm">{{ __('Kirada tracks the status — it does not move money.') }}</p>
        </section>

        {{-- Maintenance flow --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('How Maintenance Requests Work') }}</h2>
            <div class="mt-4 rounded-xl bg-slate-50 border border-slate-200 p-6 not-prose">
                <div class="flex flex-col gap-3 text-sm font-medium text-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-orange-500 text-white text-xs">1</span>
                        {{ __('Tenant reports issue (photo + description)') }}
                    </div>
                    <div class="ml-3.5 h-4 border-l border-slate-300"></div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-orange-500 text-white text-xs">2</span>
                        {{ __('Landlord reviews and sets priority') }}
                    </div>
                    <div class="ml-3.5 h-4 border-l border-slate-300"></div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-orange-500 text-white text-xs">3</span>
                        {{ __('Assigned to maintenance worker') }}
                    </div>
                    <div class="ml-3.5 h-4 border-l border-slate-300"></div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-kirada-green text-white text-xs">4</span>
                        {{ __('Worker updates status and marks "Resolved"') }}
                    </div>
                </div>
            </div>
        </section>

        {{-- Contracts flow --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('How Digital Contracts Work') }}</h2>
            <div class="mt-4 rounded-xl bg-slate-50 border border-slate-200 p-6 not-prose">
                <div class="flex flex-col gap-3 text-sm font-medium text-slate-700">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-purple-500 text-white text-xs">1</span>
                        {{ __('Landlord creates contract with terms') }}
                    </div>
                    <div class="ml-3.5 h-4 border-l border-slate-300"></div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-purple-500 text-white text-xs">2</span>
                        {{ __('System generates PDF and stores it securely') }}
                    </div>
                    <div class="ml-3.5 h-4 border-l border-slate-300"></div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-purple-500 text-white text-xs">3</span>
                        {{ __('Signing link sent to tenant') }}
                    </div>
                    <div class="ml-3.5 h-4 border-l border-slate-300"></div>
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-full bg-kirada-green text-white text-xs">4</span>
                        {{ __('Tenant reviews and signs — signed PDF archived') }}
                    </div>
                </div>
            </div>
            <p class="mt-3 text-sm">{{ __('Electronic signatures may carry legal validity depending on your jurisdiction. For important agreements, consult a qualified attorney.') }}</p>
        </section>

        {{-- Security --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('Account Security') }}</h2>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('Two-factor authentication available in Settings → Security') }}</li>
                <li>{{ __('Role-based access — each user only sees data for their role') }}</li>
                <li>{{ __('All traffic encrypted via HTTPS') }}</li>
                <li>{{ __('Every user has their own account — no password sharing') }}</li>
            </ul>
        </section>

        {{-- Target regions --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('Regions') }}</h2>
            <p class="mt-3">{{ __('Kirada is designed for landlords and tenants in:') }}</p>
            <div class="mt-3 flex flex-wrap gap-2 not-prose">
                <span class="rounded-full bg-kirada-ocean/10 px-4 py-1.5 text-sm font-medium text-kirada-ocean">🇩🇯 {{ __('Djibouti') }}</span>
                <span class="rounded-full bg-kirada-ocean/10 px-4 py-1.5 text-sm font-medium text-kirada-ocean">🇪🇹 {{ __('Ethiopia') }}</span>
                <span class="rounded-full bg-kirada-ocean/10 px-4 py-1.5 text-sm font-medium text-kirada-ocean">🇸🇴 {{ __('Somalia') }}</span>
                <span class="rounded-full bg-kirada-ocean/10 px-4 py-1.5 text-sm font-medium text-kirada-ocean">🌍 {{ __('Gulf Countries') }}</span>
                <span class="rounded-full bg-kirada-ocean/10 px-4 py-1.5 text-sm font-medium text-kirada-ocean">🇺🇸 {{ __('United States') }}</span>
            </div>
        </section>

        {{-- Help --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('Need Help?') }}</h2>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('In-app AI assistant — ask questions about using Kirada') }}</li>
                <li>{{ __('In-app messaging — contact your landlord or tenant') }}</li>
                <li>{{ __('Email support') }}: <a href="mailto:buildwithabdallah@gmail.com" class="text-kirada-ocean underline">buildwithabdallah@gmail.com</a></li>
            </ul>
        </section>

        </div>
    </article>
</x-layouts::legal>