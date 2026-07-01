<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->boolean('auto_generate_invoices')->default(true)->after('payment_due_day');
            $table->unsignedTinyInteger('invoice_generation_days_before_due')->default(7)->after('auto_generate_invoices');
            $table->unsignedTinyInteger('grace_period_days')->default(5)->after('invoice_generation_days_before_due');
            $table->string('late_fee_type', 20)->default('none')->after('grace_period_days');
            $table->decimal('late_fee_amount', 10, 2)->nullable()->after('late_fee_type');
            $table->string('late_fee_frequency', 20)->default('once')->after('late_fee_amount');
            $table->json('reminder_schedule')->nullable()->after('late_fee_frequency');
        });
    }

    public function down(): void
    {
        Schema::table('leases', function (Blueprint $table) {
            $table->dropColumn([
                'auto_generate_invoices',
                'invoice_generation_days_before_due',
                'grace_period_days',
                'late_fee_type',
                'late_fee_amount',
                'late_fee_frequency',
                'reminder_schedule',
            ]);
        });
    }
};
