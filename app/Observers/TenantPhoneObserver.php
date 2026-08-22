<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Services\WhatsApp\InboundMessageRecorder;
use Illuminate\Support\Facades\Log;

/**
 * Keeps inbound WhatsApp attribution in step with the tenant's phone number.
 *
 * Inbound messages carry no landlord or tenant identity — the sending number is
 * the only link — so attribution is resolved against the tenant list at the
 * moment the message arrives. That makes the phone number load-bearing: correct
 * a typo in it and every message that used to match becomes unreachable, while
 * the ones that now match stay filed as unmatched, which no landlord can see.
 *
 * Re-running the match on create and on any phone change keeps the inbox
 * consistent with the tenant record instead of with a snapshot of it.
 */
class TenantPhoneObserver
{
    public function created(Tenant $tenant): void
    {
        $this->reattribute($tenant);
    }

    public function updated(Tenant $tenant): void
    {
        if (! $tenant->wasChanged('phone')) {
            return;
        }

        $this->reattribute($tenant);
    }

    private function reattribute(Tenant $tenant): void
    {
        if (blank($tenant->phone)) {
            return;
        }

        $changed = app(InboundMessageRecorder::class)->reattribute($tenant);

        if ($changed > 0) {
            Log::info('whatsapp.inbound.reattributed', [
                'tenant_id' => $tenant->id,
                'messages' => $changed,
            ]);
        }
    }
}
