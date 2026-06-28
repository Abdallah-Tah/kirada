<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_signatures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            $table->string('party_role'); // bailleur, preneur, temoin
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('typed_name')->nullable(); // full legal name typed at signing time
            $table->unsignedInteger('sign_order')->default(1);
            $table->string('token', 64)->unique();
            $table->enum('status', ['pending', 'signed', 'declined'])->default('pending');
            $table->longText('signature_data')->nullable(); // base64-encoded PNG drawn signature
            $table->string('signature_hash')->nullable();    // integrity hash of the signed payload
            $table->timestamp('signed_at')->nullable();
            $table->string('signed_ip', 45)->nullable();
            $table->text('signed_user_agent')->nullable();
            $table->text('decline_reason')->nullable();
            $table->timestamps();

            $table->index(['contract_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_signatures');
    }
};
