<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rent_invoices', function (Blueprint $table) {
            $table->enum('status', [
                'draft', 'scheduled', 'sent',
                'unpaid', 'partially_paid', 'paid',
                'overdue', 'cancelled',
            ])->default('draft')->change();
        });
    }

    public function down(): void
    {
        Schema::table('rent_invoices', function (Blueprint $table) {
            $table->enum('status', [
                'draft', 'unpaid', 'partially_paid', 'paid', 'overdue', 'cancelled',
            ])->default('draft')->change();
        });
    }
};
