<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['terms-of-service', 'privacy-policy'])->index();
            $table->string('version', 20);
            $table->date('effective_date');
            $table->string('content_hash', 64)->comment('SHA-256 hash of the document content');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'version']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};