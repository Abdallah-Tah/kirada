<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Lease;
use App\Models\RentInvoice;
use App\Models\RentPayment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    /**
     * Upload a document to the private disk and create the record.
     */
    public function uploadDocument(array $data, UploadedFile $file, User $uploader): Document
    {
        if (! in_array($file->getMimeType(), self::ALLOWED_MIME_TYPES, true)) {
            throw new \DomainException('Only PDF, JPG, PNG, and WebP files are allowed.');
        }

        $data = $this->normalizeLinkedEntities($data, $uploader);
        $path = $file->store('documents', 'private');

        return Document::create([
            ...$data,
            'uploaded_by'       => $uploader->id,
            'file_path'         => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $file->getMimeType(),
            'size'              => $file->getSize(),
        ]);
    }

    /**
     * Get documents visible to a user (role-based).
     */
    public function getDocumentsForUser(User $user): Builder
    {
        $query = Document::query()
            ->with([
                'tenant:id,first_name,last_name',
                'lease:id,id',
                'rentInvoice:id,invoice_number',
                'rentPayment:id,payment_number',
                'uploader:id,name',
            ])
            ->latest();

        if ($user->hasRole('admin')) {
            // All documents
        } elseif ($user->hasRole('landlord')) {
            $query->forLandlord($user->id);
        } elseif ($user->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $user->id)->first();
            if ($tenant) {
                $query->forTenant($tenant->id);
            } else {
                $query->whereRaw('1 = 0');
            }
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query;
    }

    /**
     * Download a document — returns the file response or null if not found.
     */
    public function downloadDocument(Document $document)
    {
        if (!Storage::disk('private')->exists($document->file_path)) {
            return null;
        }

        return Storage::disk('private')->download(
            $document->file_path,
            $document->original_filename
        );
    }

    /**
     * Delete a document and its file.
     */
    public function deleteDocument(Document $document): void
    {
        if (Storage::disk('private')->exists($document->file_path)) {
            Storage::disk('private')->delete($document->file_path);
        }

        $document->delete();
    }

    /**
     * Resolve and validate linked records so uploaded documents cannot stitch
     * together entities from different landlord accounts.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function normalizeLinkedEntities(array $data, User $uploader): array
    {
        $landlordId = $uploader->hasRole('landlord') ? $uploader->id : ($data['landlord_id'] ?? null);

        if ($uploader->hasRole('tenant')) {
            $tenant = Tenant::where('user_id', $uploader->id)->firstOrFail();
            $data['tenant_id'] = $tenant->id;
            $landlordId = $tenant->landlord_id;
        } elseif (! empty($data['tenant_id'])) {
            $tenant = Tenant::findOrFail($data['tenant_id']);
            $landlordId ??= $tenant->landlord_id;
            $this->assertSameLandlord($landlordId, $tenant->landlord_id);
        }

        if (! empty($data['lease_id'])) {
            $lease = Lease::findOrFail($data['lease_id']);
            $landlordId ??= $lease->landlord_id;
            $this->assertSameLandlord($landlordId, $lease->landlord_id);
            $this->assertOptionalSame($data['tenant_id'] ?? null, $lease->tenant_id);
        }

        if (! empty($data['rent_invoice_id'])) {
            $invoice = RentInvoice::findOrFail($data['rent_invoice_id']);
            $landlordId ??= $invoice->landlord_id;
            $this->assertSameLandlord($landlordId, $invoice->landlord_id);
            $this->assertOptionalSame($data['tenant_id'] ?? null, $invoice->tenant_id);
            $this->assertOptionalSame($data['lease_id'] ?? null, $invoice->lease_id);
        }

        if (! empty($data['rent_payment_id'])) {
            $payment = RentPayment::findOrFail($data['rent_payment_id']);
            $landlordId ??= $payment->landlord_id;
            $this->assertSameLandlord($landlordId, $payment->landlord_id);
            $this->assertOptionalSame($data['tenant_id'] ?? null, $payment->tenant_id);
            $this->assertOptionalSame($data['lease_id'] ?? null, $payment->lease_id);
            $this->assertOptionalSame($data['rent_invoice_id'] ?? null, $payment->rent_invoice_id);
        }

        if (! $landlordId) {
            throw new \DomainException('Document must resolve to a landlord account.');
        }

        $data['landlord_id'] = $landlordId;

        return $data;
    }

    private function assertSameLandlord(int|string|null $expected, int|string|null $actual): void
    {
        if ((int) $expected !== (int) $actual) {
            throw new \DomainException('Linked document records must belong to the same landlord.');
        }
    }

    private function assertOptionalSame(int|string|null $expected, int|string|null $actual): void
    {
        if ($expected !== null && (int) $expected !== (int) $actual) {
            throw new \DomainException('Linked document records do not match each other.');
        }
    }
}
