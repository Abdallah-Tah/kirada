<?php

namespace App\Actions\Fortify;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\LegalAcceptance;
use App\Models\LegalDocument;
use App\Models\Plan;
use App\Models\User;
use App\Services\SubscriptionService;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules, ProfileValidationRules;

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

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'terms_accepted_at' => now(),
            'privacy_accepted_at' => now(),
        ]);

        $user->assignRole('landlord');

        // Record legal acceptances for audit trail
        $this->recordAcceptance($user, 'terms-of-service', $input);
        $this->recordAcceptance($user, 'privacy-policy', $input);

        $plan = isset($input['selected_plan'])
            ? Plan::active()->where('slug', $input['selected_plan'])->first()
            : null;

        app(SubscriptionService::class)->startTrial($user, $plan);

        return $user;
    }

    /**
     * Record a legal document acceptance for the audit trail.
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