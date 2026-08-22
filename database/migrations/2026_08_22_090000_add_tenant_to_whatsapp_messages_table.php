<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Inbound messages only ever recorded the landlord they were matched to.
     * Keeping the tenant as well makes the match auditable — you can see who
     * the number resolved to, not just whose portfolio it landed in — and lets
     * a tenant's phone number being corrected re-run the match instead of
     * silently orphaning every message that used to resolve through it.
     */
    public function up(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->foreignId('tenant_id')->nullable()->after('landlord_id')
                ->constrained('tenants')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('whatsapp_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tenant_id');
        });
    }
};
