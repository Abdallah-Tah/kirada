<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

    /** Roles a visitor may choose for themselves at registration. */
    public const SELF_SERVICE_ROLES = ['landlord', 'maintenance'];

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
            'account_type' => ['nullable', 'string', Rule::in(self::SELF_SERVICE_ROLES)],
            'selected_plan' => [
                'nullable',
                'string',
                Rule::exists('plans', 'slug')->where('is_active', true),
            ],
            'terms_accepted' => ['required', 'accepted'],
            'privacy_accepted' => ['required', 'accepted'],
        ], [
            'terms_accepted.accepted' => __('You must accept the Terms of Service to register.'),
            'privacy_accepted.accepted' => __('You must accept the Privacy Policy to register.'),
        ])->validate();

        // Only landlord and maintenance can be self-selected. Tenants arrive via
        // an invitation link and admins are seeded — neither is registerable here.
        $role = $input['account_type'] ?? 'landlord';

        // Roles come from RolePermissionSeeder. If it was never run, assignRole()
        // throws *after* the user row is committed, leaving an orphan that blocks
        // re-registration on the unique email. Check before writing anything.
        if (! Role::where('name', $role)->where('guard_name', 'web')->exists()) {
            throw ValidationException::withMessages([
                'email' => __('Registration is temporarily unavailable. Please contact support.'),
            ])->status(503);
        }

        // A registration is one unit of work: user + role + legal audit trail +
        // trial. A partial failure must leave no user behind.
        return DB::transaction(function () use ($input, $role): User {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'],
                'terms_accepted_at' => now(),
                'privacy_accepted_at' => now(),
            ]);

            $user->assignRole($role);

            // Record legal acceptances for audit trail
            $this->recordAcceptance($user, 'terms-of-service', $input);
            $this->recordAcceptance($user, 'privacy-policy', $input);

            // Maintenance providers are paid by the landlords who hire them, not by
            // Kirada subscription — starting a trial would put them behind a paywall
            // they can never clear.
            if ($role === 'landlord') {
                $plan = isset($input['selected_plan'])
                    ? Plan::active()->where('slug', $input['selected_plan'])->first()
                    : null;

                app(SubscriptionService::class)->startTrial($user, $plan);
            }

            return $user;
        });
    }

    /**
     * Record a legal document acceptance for the audit trail.
     *
     * @param  array<string, string>  $input
     */
    private function recordAcceptance(User $user, string $type, array $input): void
    {
        $document = LegalDocument::activeFor($type);

        LegalAcceptance::create([
            'user_id' => $user->id,
            'legal_document_id' => $document?->id,
            'document_type' => $type,
            'document_version' => $document?->version ?? '1.0',
            'accepted_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
