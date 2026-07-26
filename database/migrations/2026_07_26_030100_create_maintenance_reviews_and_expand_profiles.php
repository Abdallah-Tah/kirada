<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_profiles', function (Blueprint $table): void {
            $table->string('headline')->nullable()->after('business_name');
            $table->string('availability_status')->default('available')->after('years_experience');
            $table->json('languages')->nullable()->after('service_areas');
            $table->string('website')->nullable()->after('whatsapp');
        });

        Schema::create('maintenance_reviews', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('maintenance_request_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('maintenance_user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->unsignedTinyInteger('quality_rating');
            $table->unsignedTinyInteger('communication_rating');
            $table->unsignedTinyInteger('professionalism_rating');
            $table->string('title')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->index(['maintenance_user_id', 'created_at']);
            $table->index(['landlord_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_reviews');

        Schema::table('maintenance_profiles', function (Blueprint $table): void {
            $table->dropColumn(['headline', 'availability_status', 'languages', 'website']);
        });
    }
};
