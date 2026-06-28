<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\ContractSignature;
use App\Models\Document;
use App\Models\Lease;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

/**
 * Orchestrates the contract lifecycle: generate → send → sign → complete.
 *
 * Signing is performed in-app (DocuSign-style): each party gets a unique,
 * tokenised signing link, draws a signature, and the service captures an
 * audit trail (timestamp, IP, user-agent, integrity hash). When every party
 * has signed, a self-contained signed document is rendered and archived.
 */
class ContractService
{
    public function __construct(private ContractTemplateService $templates)
    {
    }

    /**
     * Build a draft contract (and its signer rows) from a lease.
     *
     * @param  array<string, mixed>  $variables  Overrides/edits to the prefilled set.
     */
    public function createFromLease(Lease $lease, array $variables, User $creator, string $type = 'bail_commercial'): Contract
    {
        $merged = array_merge($this->templates->buildVariablesFromLease($lease), $variables);

        return $this->create([
            'landlord_id' => $lease->landlord_id,
            'lease_id'    => $lease->id,
            'property_id' => $lease->property_id,
            'unit_id'     => $lease->unit_id,
            'tenant_id'   => $lease->tenant_id,
        ], $merged, $creator, $type);
    }

    /**
     * Persist a draft contract, render its body snapshot, and create signers.
     *
     * @param  array<string, mixed>  $attributes  Ownership/link columns.
     * @param  array<string, mixed>  $variables
     */
    public function create(array $attributes, array $variables, User $creator, string $type = 'bail_commercial'): Contract
    {
        return DB::transaction(function () use ($attributes, $variables, $creator, $type) {
            $contract = Contract::create([
                ...$attributes,
                'created_by' => $creator->id,
                'reference'  => $this->generateReference($type),
                'type'       => $type,
                'title'      => $variables['title'] ?? $this->defaultTitle($type, $variables),
                'locale'     => 'fr',
                'status'     => 'draft',
                'body_html'  => $this->templates->render($type, $variables),
                'variables'  => $variables,
            ]);

            $this->createSigner($contract, 'bailleur', $variables['bailleur_name'] ?? '', $variables['bailleur_email'] ?? null, 1);
            $this->createSigner($contract, 'preneur', $variables['preneur_name'] ?? '', $variables['preneur_email'] ?? null, 2);

            return $contract;
        });
    }

    /**
     * Move a draft into the "awaiting signatures" state.
     */
    public function send(Contract $contract): Contract
    {
        if (! $contract->isDraft()) {
            return $contract;
        }

        $contract->update([
            'status'  => 'sent',
            'sent_at' => Carbon::now(),
        ]);

        return $contract->fresh();
    }

    public function cancel(Contract $contract): Contract
    {
        $contract->update(['status' => 'cancelled']);

        return $contract->fresh();
    }

    /**
     * Record a drawn signature for one party, then complete the contract if
     * every party has now signed.
     */
    public function recordSignature(ContractSignature $signature, string $signatureData, ?string $ip, ?string $userAgent): ContractSignature
    {
        $signedAt = Carbon::now();

        $signature->update([
            'status'            => 'signed',
            'signature_data'    => $signatureData,
            'signature_hash'    => $this->signatureHash($signature, $signatureData, $signedAt),
            'signed_at'         => $signedAt,
            'signed_ip'         => $ip,
            'signed_user_agent' => $userAgent,
        ]);

        $contract = $signature->contract()->first();

        if ($contract && $contract->status === 'draft') {
            // A signature implies the contract is in circulation.
            $contract->update(['status' => 'sent', 'sent_at' => $contract->sent_at ?? $signedAt]);
        }

        if ($contract) {
            $this->finalizeIfComplete($contract->fresh());
        }

        return $signature->fresh();
    }

    public function decline(ContractSignature $signature, ?string $reason = null): ContractSignature
    {
        $signature->update([
            'status'         => 'declined',
            'decline_reason' => $reason,
        ]);

        $signature->contract()->update(['status' => 'declined']);

        return $signature->fresh();
    }

    /**
     * When all parties have signed, archive a self-contained signed document
     * and mark the contract completed. Idempotent: generates at most once.
     */
    public function finalizeIfComplete(Contract $contract): void
    {
        $contract->loadMissing('signatures');

        if (! $contract->allSigned() || $contract->document_id) {
            return;
        }

        $document = $this->generateSignedDocument($contract);

        $contract->update([
            'status'       => 'completed',
            'completed_at' => Carbon::now(),
            'document_id'  => $document->id,
        ]);
    }

    /**
     * Render the fully signed contract (body + signatures + audit certificate)
     * and store it as a Document on the private disk.
     */
    public function generateSignedDocument(Contract $contract): Document
    {
        $contract->loadMissing(['signatures', 'landlord', 'tenant']);

        $html = View::make('contracts.document', [
            'contract'  => $contract,
            'finalized' => true,
        ])->render();

        $path = 'contracts/'.$contract->reference.'.html';
        Storage::disk('private')->put($path, $html);

        return Document::create([
            'landlord_id'       => $contract->landlord_id,
            'tenant_id'         => $contract->tenant_id,
            'lease_id'          => $contract->lease_id,
            'uploaded_by'       => $contract->created_by ?? $contract->landlord_id,
            'title'             => $contract->title.' — '.$contract->reference,
            'type'              => 'lease_agreement',
            'file_path'         => $path,
            'original_filename' => $contract->reference.'.html',
            'mime_type'         => 'text/html',
            'size'              => strlen($html),
            'visibility'        => 'tenant_visible',
        ]);
    }

    // ── Internals ──────────────────────────────────────

    protected function createSigner(Contract $contract, string $role, string $name, ?string $email, int $order): ContractSignature
    {
        return $contract->signatures()->create([
            'party_role' => $role,
            'name'       => $name !== '' ? $name : ucfirst($role),
            'email'      => $email,
            'sign_order' => $order,
            'token'      => $this->uniqueToken(),
            'status'     => 'pending',
        ]);
    }

    protected function uniqueToken(): string
    {
        do {
            $token = Str::random(48);
        } while (ContractSignature::where('token', $token)->exists());

        return $token;
    }

    protected function generateReference(string $type): string
    {
        $prefix = 'KIR-'.strtoupper(substr(preg_replace('/[^a-z]/i', '', $type), 0, 2) ?: 'CT').'-'.date('Y').'-';

        do {
            $reference = $prefix.str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Contract::withTrashed()->where('reference', $reference)->exists());

        return $reference;
    }

    protected function defaultTitle(string $type, array $variables): string
    {
        $label = match ($type) {
            'bail_commercial' => 'Bail commercial',
            default           => 'Contrat',
        };

        $who = $variables['preneur_name'] ?? null;

        return $who ? $label.' — '.$who : $label;
    }

    protected function signatureHash(ContractSignature $signature, string $signatureData, Carbon $signedAt): string
    {
        return hash('sha256', implode('|', [
            $signature->contract_id,
            $signature->id,
            $signature->token,
            $signedAt->toIso8601String(),
            $signatureData,
        ]));
    }
}
