<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tenant_invitations', function (Blueprint $table): void {
            $table->string('whatsapp_status')->nullable()->after('whatsapp_message_id');
            $table->timestamp('whatsapp_delivered_at')->nullable()->after('whatsapp_sent_at');
            $table->timestamp('whatsapp_read_at')->nullable()->after('whatsapp_delivered_at');
            $table->timestamp('whatsapp_failed_at')->nullable()->after('whatsapp_read_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenant_invitations', function (Blueprint $table): void {
            $table->dropColumn([
                'whatsapp_status',
                'whatsapp_delivered_at',
                'whatsapp_read_at',
                'whatsapp_failed_at',
            ]);
        });
    }
};
