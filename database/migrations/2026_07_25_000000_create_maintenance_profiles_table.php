<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->string('business_name');
            $table->text('bio')->nullable();

            // Trades offered and areas served, both free-form lists rendered as
            // filter chips in the directory.
            $table->json('trades');
            $table->json('service_areas');

            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('hourly_rate')->nullable();
            $table->unsignedInteger('callout_fee')->nullable();

            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->unsignedSmallInteger('years_experience')->nullable();

            // Whether the provider has opted into appearing in the directory.
            $table->boolean('is_published')->default(false);

            // Admin credential review — drives the "Verified" badge.
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->index(['is_published', 'verified_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_profiles');
    }
};
