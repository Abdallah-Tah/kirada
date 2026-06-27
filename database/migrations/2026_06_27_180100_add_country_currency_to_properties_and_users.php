<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Add country_id + currency_id to properties
        Schema::table('properties', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('landlord_id')->constrained()->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->after('country_id')->constrained()->nullOnDelete();
        });

        // Add country_id, preferred_language, phone_country_code to users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('country_id')->nullable()->after('email')->constrained()->nullOnDelete();
            $table->string('preferred_language', 5)->nullable()->after('country_id');
            $table->string('phone_country_code', 6)->nullable()->after('preferred_language');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn(['country_id', 'preferred_language', 'phone_country_code']);
        });

        Schema::table('properties', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropForeign(['currency_id']);
            $table->dropColumn(['country_id', 'currency_id']);
        });
    }
};