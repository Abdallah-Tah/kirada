<x-layouts::legal>
    <article class="prose prose-slate max-w-none">
        <header class="mb-8 not-prose">
            <h1 class="text-3xl font-bold text-slate-900">{{ __('Privacy Policy') }}</h1>
            <p class="mt-2 text-sm text-slate-500">{{ __('Last updated') }}: June 30, 2026</p>
        </header>

        <div class="space-y-8 text-slate-700 leading-relaxed">

        {{-- Section 1 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('1. Introduction') }}</h2>
            <p class="mt-3">{{ __('This Privacy Policy explains how Build With Abdallah ("we", "us", "our") collects, uses, and protects your information when you use Kirada ("the Service") at') }} <a href="https://kirada.buildwithabdallah.com" class="text-kirada-ocean underline">kirada.buildwithabdallah.com</a>.</p>
        </section>

        {{-- Section 2 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('2. Information We Collect') }}</h2>

            <h3 class="mt-4 font-semibold text-slate-800">{{ __('2.1 Account Information') }}</h3>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('Full name') }}</li>
                <li>{{ __('Email address') }}</li>
                <li>{{ __('Phone number (if provided)') }}</li>
                <li>{{ __('Role (admin, landlord, tenant, maintenance)') }}</li>
                <li>{{ __('Password (hashed — never stored in plain text)') }}</li>
            </ul>

            <h3 class="mt-4 font-semibold text-slate-800">{{ __('2.2 Property and Lease Data') }}</h3>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('Property addresses and unit details') }}</li>
                <li>{{ __('Tenant names and contact information') }}</li>
                <li>{{ __('Lease terms, dates, and rent amounts') }}</li>
                <li>{{ __('Invoice and payment status records (no financial account data)') }}</li>
            </ul>

            <h3 class="mt-4 font-semibold text-slate-800">{{ __('2.3 User Content') }}</h3>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('Uploaded documents (contracts, lease agreements, photos)') }}</li>
                <li>{{ __('Maintenance request descriptions and attached images') }}</li>
                <li>{{ __('In-app messages between users') }}</li>
                <li>{{ __('AI assistant conversation history') }}</li>
            </ul>

            <h3 class="mt-4 font-semibold text-slate-800">{{ __('2.4 Technical Data') }}</h3>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('IP address') }}</li>
                <li>{{ __('Browser type and device information') }}</li>
                <li>{{ __('Login timestamps') }}</li>
                <li>{{ __('Usage logs and analytics') }}</li>
            </ul>
        </section>

        {{-- Section 3 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('3. How We Use Your Information') }}</h2>
            <p class="mt-3">{{ __('We use your information to:') }}</p>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('Operate and maintain the Service') }}</li>
                <li>{{ __('Authenticate users and manage sessions') }}</li>
                <li>{{ __('Display relevant data based on your role') }}</li>
                <li>{{ __('Send notifications about invoices, maintenance requests, and messages') }}</li>
                <li>{{ __('Provide the AI assistant feature') }}</li>
                <li>{{ __('Improve the Service and fix issues') }}</li>
                <li>{{ __('Prevent fraud and unauthorized access') }}</li>
            </ul>
            <p class="mt-3 font-medium">{{ __('We do NOT:') }}</p>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('Sell your personal data to third parties') }}</li>
                <li>{{ __('Share your data with advertisers') }}</li>
                <li>{{ __('Use your data for marketing without consent') }}</li>
                <li>{{ __('Process any financial transactions or store payment card information') }}</li>
            </ul>
        </section>

        {{-- Section 4 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('4. Data Sharing') }}</h2>

            <h3 class="mt-4 font-semibold text-slate-800">{{ __('4.1 Between Users') }}</h3>
            <p class="mt-2">{{ __('Information you enter is visible to authorized users within the same property context:') }}</p>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('Landlords see data for their own properties and tenants') }}</li>
                <li>{{ __('Tenants see their own lease, invoices, and maintenance requests') }}</li>
                <li>{{ __('Maintenance workers see assigned requests only') }}</li>
            </ul>

            <h3 class="mt-4 font-semibold text-slate-800">{{ __('4.2 With Third Parties') }}</h3>
            <p class="mt-2">{{ __('We do not share your data except:') }}</p>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('Hosting providers — to store and serve the application') }}</li>
                <li>{{ __('Email providers — to send notifications') }}</li>
                <li>{{ __('Legal compliance — if required by law, court order, or government authority') }}</li>
            </ul>
        </section>

        {{-- Section 5 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('5. Data Security') }}</h2>
            <p class="mt-3">{{ __('We implement reasonable security measures including:') }}</p>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('Password hashing (bcrypt)') }}</li>
                <li>{{ __('Two-factor authentication') }}</li>
                <li>{{ __('HTTPS encryption via Cloudflare') }}</li>
                <li>{{ __('Role-based access control') }}</li>
                <li>{{ __('Regular security updates') }}</li>
            </ul>
            <p class="mt-3">{{ __('No system is 100% secure. You are responsible for using a strong password, enabling 2FA, and not sharing credentials.') }}</p>
        </section>

        {{-- Section 6 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('6. Data Retention') }}</h2>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li>{{ __('Your data is retained while your account is active') }}</li>
                <li>{{ __('You may request account deletion at any time') }}</li>
                <li>{{ __('Anonymized usage logs may be retained for up to 90 days') }}</li>
                <li>{{ __('Signed contract documents are retained per the landlord\'s settings') }}</li>
            </ul>
        </section>

        {{-- Section 7 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('7. Your Rights') }}</h2>
            <ul class="mt-2 list-disc pl-6 space-y-1">
                <li><strong>{{ __('Access') }}</strong> — {{ __('your personal data') }}</li>
                <li><strong>{{ __('Correct') }}</strong> — {{ __('inaccurate information') }}</li>
                <li><strong>{{ __('Delete') }}</strong> — {{ __('your account and associated data') }}</li>
                <li><strong>{{ __('Export') }}</strong> — {{ __('your data (contact us)') }}</li>
                <li><strong>{{ __('Object') }}</strong> — {{ __('to specific data uses') }}</li>
            </ul>
            <p class="mt-3">{{ __('To exercise these rights, email') }} <a href="mailto:buildwithabdallah@gmail.com" class="text-kirada-ocean underline">buildwithabdallah@gmail.com</a>.</p>
        </section>

        {{-- Section 8 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('8. AI Assistant Data') }}</h2>
            <p class="mt-3">{{ __('AI conversations are stored to improve response quality. This data is not shared with third parties, not used for training external models, accessible only to your account, and deletable upon account deletion.') }}</p>
        </section>

        {{-- Section 9 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('9. Cookies') }}</h2>
            <p class="mt-3">{{ __('The Service uses essential cookies for session authentication, language preference, PWA functionality, and CSRF protection. We do not use advertising or tracking cookies.') }}</p>
        </section>

        {{-- Section 10 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('10. Children\'s Privacy') }}</h2>
            <p class="mt-3">{{ __('The Service is not directed at children under 18. We do not knowingly collect data from minors.') }}</p>
        </section>

        {{-- Section 11 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('11. International Users') }}</h2>
            <p class="mt-3">{{ __('Kirada is hosted in the United States. If you access the Service from outside the US, your data is transferred to and processed in the United States. By using the Service, you consent to this transfer.') }}</p>
        </section>

        {{-- Section 12 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('12. Data Breach Notification') }}</h2>
            <p class="mt-3">{{ __('In the event of a data breach, we will notify affected users within 72 hours, provide details of exposed data, recommend protective steps, and take corrective action.') }}</p>
        </section>

        {{-- Section 13 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('13. Changes to This Policy') }}</h2>
            <p class="mt-3">{{ __('We may update this Privacy Policy from time to time. Material changes will be notified through the app or email.') }}</p>
        </section>

        {{-- Section 14 --}}
        <section>
            <h2 class="text-xl font-semibold text-slate-900">{{ __('14. Contact') }}</h2>
            <p class="mt-3">
                {{ __('Email') }}: <a href="mailto:buildwithabdallah@gmail.com" class="text-kirada-ocean underline">buildwithabdallah@gmail.com</a><br>
                {{ __('Website') }}: <a href="https://buildwithabdallah.com" class="text-kirada-ocean underline">buildwithabdallah.com</a>
            </p>
        </section>

        </div>
    </article>
</x-layouts::legal>