<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DocumentService
{
    /**
     * Upload a document to the private disk and create the record.
     */
    public function uploadDocument(array $data, UploadedFile $file, User $uploader): Document
    {
        $path = $file->store('documents', 'private');

        // Resolve landlord_id based on uploader role if not set
        if (!isset($data['landlord_id']) || !$data['landlord_id']) {
            if ($uploader->hasRole('landlord')) {
                $data['landlord_id'] = $uploader->id;
            } elseif ($uploader->hasRole('tenant') && isset($data['tenant_id'])) {
                $tenant = Tenant::find($data['tenant_id']);
                $data['landlord_id'] = $tenant?->landlord_id;
            }
        }

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
}
