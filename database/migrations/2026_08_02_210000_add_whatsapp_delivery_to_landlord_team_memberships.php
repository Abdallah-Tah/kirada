<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bring team invitations to parity with tenant invitations: a phone number, a
 * per-invitation channel choice, and the WhatsApp delivery trail the BWA
 * webhook writes back to.
 *
 * Purely additive and nullable — existing memberships keep working as
 * email-only until a landlord picks WhatsApp.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('landlord_team_memberships', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->json('delivery_channels')->nullable()->after('phone');
            $table->string('whatsapp_message_id')->nullable()->after('accepted_at');
            $table->uuid('whatsapp_request_id')->nullable()->after('whatsapp_message_id');
            $table->string('whatsapp_status')->nullable()->after('whatsapp_request_id');
            $table->timestamp('whatsapp_sent_at')->nullable()->after('whatsapp_status');
            $table->timestamp('whatsapp_delivered_at')->nullable()->after('whatsapp_sent_at');
            $table->timestamp('whatsapp_read_at')->nullable()->after('whatsapp_delivered_at');
            $table->timestamp('whatsapp_failed_at')->nullable()->after('whatsapp_read_at');
            $table->text('whatsapp_error')->nullable()->after('whatsapp_failed_at');

            // The webhook looks memberships up by provider message id.
            $table->index('whatsapp_message_id');
        });
    }

    public function down(): void
    {
        Schema::table('landlord_team_memberships', function (Blueprint $table) {
            $table->dropIndex(['whatsapp_message_id']);
            $table->dropColumn([
                'phone',
                'delivery_channels',
                'whatsapp_message_id',
                'whatsapp_request_id',
                'whatsapp_status',
                'whatsapp_sent_at',
                'whatsapp_delivered_at',
                'whatsapp_read_at',
                'whatsapp_failed_at',
                'whatsapp_error',
            ]);
        });
    }
};
