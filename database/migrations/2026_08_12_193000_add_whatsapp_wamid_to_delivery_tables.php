<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Meta's status webhooks are keyed by wamid, but our outbound records store the
 * gateway's own message id, because the gateway queues the send before Meta has
 * issued a wamid. The gateway's first status event carries both, so we capture
 * the wamid there and can then match Meta's callbacks directly.
 *
 * Additive and nullable: no existing row is rewritten.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_deliveries', function (Blueprint $table): void {
            $table->string('provider_wamid')->nullable()->after('provider_message_id')->index();
        });

        Schema::table('tenant_invitations', function (Blueprint $table): void {
            $table->string('whatsapp_wamid')->nullable()->after('whatsapp_message_id')->index();
        });

        Schema::table('landlord_team_memberships', function (Blueprint $table): void {
            $table->string('whatsapp_wamid')->nullable()->after('whatsapp_message_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('notification_deliveries', function (Blueprint $table): void {
            $table->dropIndex(['provider_wamid']);
            $table->dropColumn('provider_wamid');
        });

        Schema::table('tenant_invitations', function (Blueprint $table): void {
            $table->dropIndex(['whatsapp_wamid']);
            $table->dropColumn('whatsapp_wamid');
        });

        Schema::table('landlord_team_memberships', function (Blueprint $table): void {
            $table->dropIndex(['whatsapp_wamid']);
            $table->dropColumn('whatsapp_wamid');
        });
    }
};
