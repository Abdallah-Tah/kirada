<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The original table could only express "approved" (a nullable approved_at).
     * A directory needs a request that is awaiting the other side's consent, and
     * a way to record a decline, so the link gets an explicit status.
     */
    public function up(): void
    {
        Schema::table('landlord_maintenance', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('maintenance_user_id');
            $table->string('requested_by', 20)->default('landlord')->after('status');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->text('message')->nullable()->after('rejected_at');

            $table->index(['maintenance_user_id', 'status']);
        });

        // Existing rows only exist because someone was already approved.
        DB::table('landlord_maintenance')
            ->whereNotNull('approved_at')
            ->update(['status' => 'approved']);
    }

    public function down(): void
    {
        Schema::table('landlord_maintenance', function (Blueprint $table) {
            $table->dropIndex(['maintenance_user_id', 'status']);
            $table->dropColumn(['status', 'requested_by', 'rejected_at', 'message']);
        });
    }
};
