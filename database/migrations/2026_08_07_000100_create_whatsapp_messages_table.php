<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('provider_message_id')->unique();
            $table->string('from_number', 32);
            $table->string('profile_name')->nullable();
            $table->string('message_type', 32);
            $table->text('body')->nullable();
            $table->string('media_id')->nullable();
            $table->json('payload');
            $table->timestamp('received_at');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['landlord_id', 'received_at']);
            $table->index(['from_number', 'received_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_messages');
    }
};
