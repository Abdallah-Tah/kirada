<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_deliveries', function (Blueprint $table) {
            $table->foreignId('rent_payment_id')
                ->nullable()
                ->after('rent_invoice_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->index(['rent_payment_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::table('notification_deliveries', function (Blueprint $table) {
            $table->dropIndex(['rent_payment_id', 'event']);
            $table->dropConstrainedForeignId('rent_payment_id');
        });
    }
};
