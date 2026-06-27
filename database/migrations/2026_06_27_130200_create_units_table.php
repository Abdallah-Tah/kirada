<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('building_id')->nullable()->constrained()->nullOnDelete();
            $table->string('unit_number');
            $table->string('floor')->nullable();
            $table->enum('type', ['apartment', 'office', 'shop', 'warehouse', 'other'])->default('apartment');
            $table->decimal('area_sqm', 8, 2)->nullable();
            $table->unsignedInteger('bedrooms')->default(0);
            $table->unsignedInteger('bathrooms')->default(0);
            $table->decimal('monthly_rent', 10, 2)->default(0);
            $table->decimal('security_deposit', 10, 2)->default(0);
            $table->enum('status', ['vacant', 'occupied', 'maintenance'])->default('vacant');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['property_id', 'unit_number']);
            $table->index(['property_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};