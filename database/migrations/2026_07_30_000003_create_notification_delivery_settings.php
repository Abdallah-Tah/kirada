<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landlord_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->json('invoice_channels');
            $table->json('reminder_channels');
            $table->boolean('auto_send_invoices')->default(true);
            $table->boolean('attach_pdf_to_email')->default(true);
            $table->timestamps();
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->json('invoice_delivery_channels')->nullable()->after('reminder_schedule');
            $table->json('reminder_delivery_channels')->nullable()->after('invoice_delivery_channels');
            $table->boolean('auto_send_invoice_override')->nullable()->after('reminder_delivery_channels');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->timestamp('whatsapp_consented_at')->nullable()->after('phone');
            $table->timestamp('whatsapp_consent_revoked_at')->nullable()->after('whatsapp_consented_at');
            $table->string('whatsapp_consent_source', 32)->nullable()->after('whatsapp_consent_revoked_at');
        });

        Schema::create('notification_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('rent_invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event', 64);
            $table->string('channel', 24);
            $table->string('status', 24)->default('queued');
            $table->string('recipient_masked', 160)->nullable();
            $table->string('provider_message_id')->nullable();
            $table->string('provider_media_id')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('error_code', 100)->nullable();
            $table->text('error_message')->nullable();
            $table->string('idempotency_key', 64)->unique();
            $table->timestamp('queued_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();

            $table->index(['rent_invoice_id', 'event']);
            $table->index(['provider_message_id']);
            $table->index(['landlord_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_deliveries');

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_consented_at',
                'whatsapp_consent_revoked_at',
                'whatsapp_consent_source',
            ]);
        });

        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn([
                'invoice_delivery_channels',
                'reminder_delivery_channels',
                'auto_send_invoice_override',
            ]);
        });

        Schema::dropIfExists('landlord_notification_settings');
    }
};
