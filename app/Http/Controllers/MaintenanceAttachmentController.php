<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceAttachment;
use Illuminate\Support\Facades\Storage;

class MaintenanceAttachmentController extends Controller
{
    public function show(MaintenanceAttachment $attachment)
    {
        $request = $attachment->maintenanceRequest;
        $this->authorize('view', $request);

        $user = auth()->user();
        abort_if(
            $attachment->is_internal && ! $user->hasRole('admin') && $request->landlord_id !== $user->id,
            403,
        );

        abort_unless(Storage::disk($attachment->disk)->exists($attachment->path), 404);

        return Storage::disk($attachment->disk)->response(
            $attachment->path,
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type ?: 'application/octet-stream'],
            'inline',
        );
    }
}
