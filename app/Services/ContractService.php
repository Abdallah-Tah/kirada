<?php

namespace App\Services;

use App\Mail\ContractCompleted;
use App\Mail\ContractSignatureRequest;
use App\Models\Contract;
use App\Models\ContractSignature;
use App\Models\Document;
use App\Models\Lease;
use App\Models\User;
use App\Support\Locales;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
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
    /** Upper bound on a drawn-signature data URL (~2 MB) to stop payload abuse. */
    private const MAX_SIGNATURE_BYTES = 2_000_000;

    /** Signing links are short-lived bearer tokens. */
    private const SIGNATURE_LINK_TTL_DAYS = 7;

    public function __construct(private ContractTemplateService $templates) {}

    /**
     * Build a draft contract (and its signer rows) from a lease.
     *
     * @param  array<string, mixed>  $variables  Overrides/edits to the prefilled set.
     */
    public function createFromLease(Lease $lease, array $variables, User $creator, string $type = 'bail_commercial'): Contract
    {
        $leaseVars = $this->templates->buildVariablesFromLease($lease);

        // Don't let empty user-submitted values clobber prefilled lease data.
        $variables = array_filter($variables, fn ($v) => $v !== null && $v !== '');

        $merged = array_merge($leaseVars, $variables);

        return $this->create([
            'landlord_id' => $lease->landlord_id,
            'lease_id' => $lease->id,
            'property_id' => $lease->property_id,
            'unit_id' => $lease->unit_id,
            'tenant_id' => $lease->tenant_id,
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
                'reference' => $this->generateReference($type),
                'type' => $type,
                'title' => $variables['title'] ?? $this->defaultTitle($type, $variables),
                'locale' => 'fr',
                'status' => 'draft',
                'body_html' => $this->templates->render($type, $variables),
                'variables' => $variables,
            ]);

            $this->createSigner($contract, 'bailleur', $variables['bailleur_name'] ?? '', $variables['bailleur_email'] ?? null, 1);
            $this->createSigner($contract, 'preneur', $variables['preneur_name'] ?? '', $variables['preneur_email'] ?? null, 2);

            return $contract;
        });
    }

    /**
     * Move a draft into the "awaiting signatures" state and email each pending
     * signer their unique signing link.
     */
    public function send(Contract $contract): Contract
    {
        if (! $contract->isDraft()) {
            return $contract;
        }

        $contract->update([
            'status' => 'sent',
            'sent_at' => Carbon::now(),
        ]);

        $contract->signatures()
            ->where('status', 'pending')
            ->update(['expires_at' => Carbon::now()->addDays(self::SIGNATURE_LINK_TTL_DAYS)]);

        $this->dispatchSignatureRequests($contract->fresh('signatures'));

        return $contract->fresh();
    }

    /**
     * Email every pending signer (with an address) their signing link.
     */
    public function dispatchSignatureRequests(Contract $contract): int
    {
        $sent = 0;

        foreach ($contract->signatures as $signature) {
            if ($signature->status === 'pending' && filled($signature->email)) {
                $this->sendSignatureRequest($signature);
                $sent++;
            }
        }

        return $sent;
    }

    /**
     * (Re)send the signing link to a single signer.
     */
    public function sendSignatureRequest(ContractSignature $signature): void
    {
        $signature->update([
            'expires_at' => Carbon::now()->addDays(self::SIGNATURE_LINK_TTL_DAYS),
        ]);

        Mail::to($signature->email)
            ->locale(Locales::forLandlord($signature->contract?->landlord))
            ->send(new ContractSignatureRequest($signature));
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
    public function recordSignature(ContractSignature $signature, string $signatureData, ?string $ip, ?string $userAgent, ?string $typedName = null): ContractSignature
    {
        $contract = $signature->contract()->first();

        // Defence in depth: this path is reachable from the public signing page,
        // so never trust the caller for state or payload shape/size.
        if ($signature->status !== 'pending') {
            throw new \RuntimeException('This signature is no longer pending.');
        }

        if (! $contract || ! $contract->isSent()) {
            throw new \RuntimeException('This contract is not open for signature.');
        }

        if ($signature->isExpired()) {
            throw new \RuntimeException('This signing link has expired.');
        }

        if (! $this->isValidSignatureImage($signatureData)) {
            throw new \InvalidArgumentException('Invalid or oversized signature payload.');
        }

        $signedAt = Carbon::now();

        $signature->update([
            'status' => 'signed',
            'signature_data' => $signatureData,
            'signature_hash' => $this->signatureHash($signature, $signatureData, $signedAt),
            'typed_name' => $typedName !== null && trim($typedName) !== '' ? trim($typedName) : null,
            'signed_at' => $signedAt,
            'signed_ip' => $ip,
            'signed_user_agent' => $userAgent,
        ]);

        $this->finalizeIfComplete($contract->fresh());

        return $signature->fresh();
    }

    /**
     * Where to send a signer once they have signed.
     *
     * The signing link is a public bearer token, so the visitor is usually
     * anonymous — they stay on the confirmation card. Only when the logged-in
     * user *is* that party do we hand them back to the app, and only to a page
     * their role can already open: the contract itself for the landlord side,
     * the role dashboard for a tenant (contract pages are landlord-only).
     *
     * Returns null whenever the visitor is not that party — including a
     * different signed-in user opening someone else's link.
     */
    public function destinationAfterSigning(ContractSignature $signature, ?User $user): ?string
    {
        if (! $user) {
            return null;
        }

        $contract = $signature->contract()->first();

        if (! $contract) {
            return null;
        }

        if ($signature->party_role === 'preneur') {
            return $contract->tenant()->first()?->user_id === $user->id
                ? route('dashboard')
                : null;
        }

        if ($signature->party_role === 'bailleur') {
            return $user->canAccessLandlordPortal() && $user->belongsToLandlordAccount($contract->landlord_id)
                ? route('contracts.show', $contract)
                : null;
        }

        return null;
    }

    public function decline(ContractSignature $signature, ?string $reason = null): ContractSignature
    {
        $signature->update([
            'status' => 'declined',
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
        // Lock the contract row and re-check inside the transaction so two
        // signers completing concurrently cannot both generate a document.
        $finalized = DB::transaction(function () use ($contract): bool {
            $locked = Contract::whereKey($contract->id)->lockForUpdate()->first();

            if (! $locked || $locked->document_id) {
                return false;
            }

            $locked->loadMissing('signatures');

            if (! $locked->allSigned()) {
                return false;
            }

            $document = $this->generateSignedDocument($locked);

            $locked->update([
                'status' => 'completed',
                'completed_at' => Carbon::now(),
                'document_id' => $document->id,
            ]);

            return true;
        });

        // Outside the transaction: the row lock is released and the document is
        // committed, so a queued mail job is guaranteed to find both. The
        // transaction returning true also makes this exactly-once.
        if ($finalized) {
            $this->dispatchCompletedCopies($contract->fresh(['signatures', 'document']));
        }
    }

    /**
     * Email every party their countersigned copy, with the signed PDF attached.
     *
     * One party failing (a bad address, a rejecting server) must not deny the
     * others their copy, so each send is isolated and logged.
     */
    public function dispatchCompletedCopies(Contract $contract): int
    {
        $sent = 0;
        $locale = Locales::forLandlord($contract->landlord);

        foreach ($contract->signatures as $signature) {
            if (blank($signature->email)) {
                continue;
            }

            try {
                Mail::to($signature->email)
                    ->locale($locale)
                    ->send(new ContractCompleted($contract, $signature));
                $sent++;
            } catch (\Throwable $e) {
                Log::warning('Could not email the signed contract copy.', [
                    'contract_id' => $contract->id,
                    'signature_id' => $signature->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $sent;
    }

    /**
     * Render the fully signed contract (body + signatures + audit certificate)
     * to a PDF and store it as a Document on the private disk.
     */
    public function generateSignedDocument(Contract $contract): Document
    {
        $contract->loadMissing(['signatures', 'landlord', 'tenant']);

        $pdf = $this->renderPdf($contract);

        $path = 'contracts/'.$contract->reference.'.pdf';
        Storage::disk('private')->put($path, $pdf);

        return Document::create([
            'landlord_id' => $contract->landlord_id,
            'tenant_id' => $contract->tenant_id,
            'lease_id' => $contract->lease_id,
            'uploaded_by' => $contract->created_by ?? $contract->landlord_id,
            'title' => $contract->title.' — '.$contract->reference,
            'type' => 'lease_agreement',
            'file_path' => $path,
            'original_filename' => $contract->reference.'.pdf',
            'mime_type' => 'application/pdf',
            'size' => strlen($pdf),
            'visibility' => 'tenant_visible',
        ]);
    }

    /**
     * Render a contract to a PDF binary string using the dompdf engine.
     *
     * Anti-tamper protections applied via the dompdf Canvas API:
     *  - Page numbers (Page X / Y) on every page footer
     *  - Contract reference + date on every page header
     */
    public function renderPdf(Contract $contract): string
    {
        $contract->loadMissing(['signatures', 'landlord', 'tenant']);
        $previousLocale = App::currentLocale();
        $generatedAt = now();
        App::setLocale('fr');

        try {
            $pdf = Pdf::loadView('contracts.document-pdf', [
                'contract' => $contract,
                'pdfGeneratedAt' => $generatedAt,
                'pdfLogoPath' => public_path('brand/kirada-logo-transparent.png'),
                'pdfSupportEmail' => config('mail.from.address'),
                'pdfDocumentDate' => $contract->created_at?->format('d/m/Y'),
            ])->setPaper('a4', 'portrait');

            // Render first so the canvas knows total page count.
            $pdf->render();

            $dompdf = $pdf->getDomPDF();
            $canvas = $dompdf->getCanvas();
            $font = $dompdf->getFontMetrics()->getFont('DejaVu Sans');
            $ref = $contract->reference;
            $date = Carbon::parse($contract->created_at)->format('d/m/Y');
            $footer = __('pdf.footer.generated', [
                'date' => $generatedAt->format('d/m/Y'),
                'time' => $generatedAt->format('H:i'),
            ]);
            $page = __('pdf.footer.page', [
                'current' => '{PAGE_NUM}',
                'total' => '{PAGE_COUNT}',
            ]);

            // Footer: generation stamp, reference and page numbers.
            $canvas->page_text(40, 818, "{$footer} — {$ref}", $font, 7.5, [0.39, 0.46, 0.56]);
            $canvas->page_text(482, 818, $page, $font, 7.5, [0.39, 0.46, 0.56]);

            // Header: reference on every page (anti-substitution).
            $canvas->page_text(400, 16, "Kirada — {$ref} — {$date}", $font, 7.5, [0.39, 0.46, 0.56]);

            return $pdf->output();
        } finally {
            App::setLocale($previousLocale);
        }
    }

    // ── Internals ──────────────────────────────────────

    protected function createSigner(Contract $contract, string $role, string $name, ?string $email, int $order): ContractSignature
    {
        $email = $email !== null && trim($email) !== '' ? trim($email) : null;

        return $contract->signatures()->create([
            'party_role' => $role,
            'name' => $name !== '' ? $name : ucfirst($role),
            'email' => $email,
            'sign_order' => $order,
            'token' => $this->uniqueToken(),
            'status' => 'pending',
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
            default => 'Contrat',
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

    protected function isValidSignatureImage(string $signatureData): bool
    {
        if (strlen($signatureData) > self::MAX_SIGNATURE_BYTES) {
            return false;
        }

        if (! preg_match('/\Adata:image\/(png|jpe?g|webp);base64,([A-Za-z0-9+\/=]+)\z/i', $signatureData, $matches)) {
            return false;
        }

        $bytes = base64_decode($matches[2], true);

        if ($bytes === false || $bytes === '') {
            return false;
        }

        $image = @getimagesizefromstring($bytes);

        if ($image === false || empty($image['mime'])) {
            return false;
        }

        return in_array(strtolower($image['mime']), ['image/png', 'image/jpeg', 'image/webp'], true);
    }
}
