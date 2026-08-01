<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_invitations', function (Blueprint $table): void {
            $table->json('delivery_channels')->nullable()->after('phone');
            $table->string('whatsapp_message_id')->nullable()->after('accepted_at');
            $table->timestamp('whatsapp_sent_at')->nullable()->after('whatsapp_message_id');
            $table->text('whatsapp_error')->nullable()->after('whatsapp_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('tenant_invitations', function (Blueprint $table): void {
            $table->dropColumn([
                'delivery_channels',
                'whatsapp_message_id',
                'whatsapp_sent_at',
                'whatsapp_error',
            ]);
        });
    }
};
