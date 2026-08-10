<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('property_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('expense_date');
            $table->string('category', 50);
            $table->decimal('amount', 14, 2);
            $table->string('payment_method', 50)->nullable();
            $table->string('vendor')->nullable();
            $table->string('description');
            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('receipt_original_filename')->nullable();
            $table->string('receipt_mime_type')->nullable();
            $table->unsignedBigInteger('receipt_size')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['landlord_id', 'expense_date']);
            $table->index(['landlord_id', 'category']);
            $table->index(['property_id', 'expense_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
