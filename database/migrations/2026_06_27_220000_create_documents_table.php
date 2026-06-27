<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('tenant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('lease_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rent_invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('rent_payment_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->enum('type', ['lease_agreement', 'payment_receipt', 'payment_proof', 'id_document', 'other'])->default('other');
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type');
            $table->unsignedBigInteger('size');
            $table->enum('visibility', ['landlord_only', 'tenant_visible', 'admin_only'])->default('landlord_only');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['landlord_id', 'type']);
            $table->index(['tenant_id', 'visibility']);
            $table->index('lease_id');
            $table->index('rent_invoice_id');
            $table->index('rent_payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};