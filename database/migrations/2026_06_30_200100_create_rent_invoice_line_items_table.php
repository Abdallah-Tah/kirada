<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_invoice_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rent_invoice_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // rent / late_fee / adjustment / discount / other
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->index('rent_invoice_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_invoice_line_items');
    }
};
