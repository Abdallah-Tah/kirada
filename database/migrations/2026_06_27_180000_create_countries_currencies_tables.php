<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // ISO 3166-1 alpha-3
            $table->string('code2', 2)->unique(); // ISO 3166-1 alpha-2
            $table->string('name');
            $table->string('dial_code', 6)->nullable(); // +253, +251, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // DJF, USD, etc.
            $table->string('name');
            $table->string('symbol', 5)->nullable(); // ₣, $, ﷼
            $table->integer('decimals')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Link countries to their default currency
        Schema::create('country_currency', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->unique(['country_id', 'currency_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_currency');
        Schema::dropIfExists('currencies');
        Schema::dropIfExists('countries');
    }
};