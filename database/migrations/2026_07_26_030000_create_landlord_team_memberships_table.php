<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landlord_team_memberships', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('role');
            $table->string('token_hash', 64)->unique();
            $table->string('status')->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
            $table->unique(['landlord_id', 'email']);
            $table->index(['landlord_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landlord_team_memberships');
    }
};
