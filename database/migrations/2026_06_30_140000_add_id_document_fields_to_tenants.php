<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('id_type', ['national_id', 'passport', 'driver_license', 'other'])->nullable()->after('national_id');
            $table->string('id_document_number')->nullable()->after('id_type');
            $table->string('id_document_path')->nullable()->after('id_document_number');
            $table->string('id_document_original_filename')->nullable()->after('id_document_path');
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn(['id_type', 'id_document_number', 'id_document_path', 'id_document_original_filename']);
        });
    }
};