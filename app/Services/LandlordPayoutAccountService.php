<?php

namespace App\Services;

use App\Models\LandlordPayoutAccount;
use App\Models\User;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class LandlordPayoutAccountService
{
    /**
     * @param  array<int, array<string, mixed>>  $accounts
     * @return Collection<int, LandlordPayoutAccount>
     */
    public function sync(User $landlord, array $accounts, ?int $primaryIndex): Collection
    {
        if (! $landlord->isLandlord()) {
            throw new DomainException('Only landlords can manage payment accounts.');
        }

        $accounts = array_values($accounts);

        if ($accounts !== [] && ($primaryIndex === null || ! array_key_exists($primaryIndex, $accounts))) {
            throw new DomainException('Select one primary payment account.');
        }

        $ownedIds = $landlord->payoutAccounts()->pluck('id');
        $submittedIds = collect($accounts)
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id);

        if ($submittedIds->diff($ownedIds)->isNotEmpty()) {
            throw new DomainException('One or more payment accounts do not belong to this landlord.');
        }

        DB::transaction(function () use ($landlord, $accounts, $primaryIndex): void {
            $landlord->payoutAccounts()->update(['is_primary' => false]);
            $keptIds = [];

            foreach ($accounts as $index => $account) {
                $attributes = [
                    'label' => trim((string) $account['label']),
                    'method' => $account['method'],
                    'account_number' => $this->nullableString($account['account_number'] ?? null),
                    'account_name' => $this->nullableString($account['account_name'] ?? null),
                    'instructions' => $this->nullableString($account['instructions'] ?? null),
                    'is_primary' => $index === $primaryIndex,
                    'is_active' => (bool) ($account['is_active'] ?? true),
                    'sort_order' => $index,
                ];

                if (filled($account['id'] ?? null)) {
                    $savedAccount = $landlord->payoutAccounts()
                        ->whereKey((int) $account['id'])
                        ->firstOrFail();
                    $savedAccount->update($attributes);
                } else {
                    $savedAccount = $landlord->payoutAccounts()->create($attributes);
                }

                $keptIds[] = $savedAccount->id;
            }

            $deleteQuery = $landlord->payoutAccounts();

            if ($keptIds !== []) {
                $deleteQuery->whereKeyNot($keptIds);
            }

            $deleteQuery->delete();
        });

        return $landlord->payoutAccounts()->get();
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
