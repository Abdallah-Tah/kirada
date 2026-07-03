<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rent_invoices', function (Blueprint $table) {
            // Short reference tenants quote when paying via Waafi / D-Money / CAC Pay.
            $table->string('payment_reference', 20)->nullable()->unique()->after('invoice_number');
            $table->foreignId('currency_id')->nullable()->after('amount')->constrained()->nullOnDelete();
        });

        Schema::table('rent_payments', function (Blueprint $table) {
            $table->foreignId('currency_id')->nullable()->after('amount')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rent_payments', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropColumn('currency_id');
        });

        Schema::table('rent_invoices', function (Blueprint $table) {
            $table->dropForeign(['currency_id']);
            $table->dropUnique(['payment_reference']);
            $table->dropColumn(['payment_reference', 'currency_id']);
        });
    }
};
