<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bwa_webhook_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('request_id')->unique();
            $table->string('payload_hash', 64);
            $table->timestamp('received_at');
            $table->timestamp('expires_at')->index();
        });

        Schema::create('bwa_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id')->unique();
            $table->string('type', 100);
            $table->string('status', 24)->default('queued');
            $table->longText('raw_body');
            $table->string('payload_hash', 64);
            $table->timestamp('occurred_at')->nullable();
            $table->timestamp('received_at');
            $table->timestamp('processed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bwa_events');
        Schema::dropIfExists('bwa_webhook_requests');
    }
};
