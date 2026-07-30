<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rent_payments', function (Blueprint $table): void {
            $table->foreignId('landlord_payout_account_id')
                ->nullable()
                ->after('landlord_id')
                ->constrained('landlord_payout_accounts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('rent_payments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('landlord_payout_account_id');
        });
    }
};
