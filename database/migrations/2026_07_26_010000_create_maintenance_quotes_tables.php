<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Maintenance quotes: a pro attaches a priced proposal to a request.
        // Once approved and the work is done, it converts to an invoice.
        Schema::create('maintenance_quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('currency_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'declined', 'invoiced', 'paid'])->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['maintenance_request_id', 'status']);
            $table->index('maintenance_user_id');
        });

        Schema::create('maintenance_quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_quote_id')->constrained()->cascadeOnDelete();
            $table->string('description');
            $table->decimal('quantity', 8, 2)->default(1);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index('maintenance_quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_quote_items');
        Schema::dropIfExists('maintenance_quotes');
    }
};
