<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Landlord payout accounts: named destinations a tenant pays into
        // (D-Money, Waafi, Cac Bank, bank transfer, cash...). A landlord can
        // have several and marks one primary, which is shown to tenants by
        // default on the manual-payment screen.
        Schema::create('landlord_payout_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('landlord_id')->constrained('users')->cascadeOnDelete();
            $table->string('label');                       // e.g. "My D-Money", "Business Waafi"
            $table->string('method');                      // d_money, waafi, cac_bank, bank_transfer, cash, other
            $table->string('account_number')->nullable();  // phone / wallet / account number
            $table->string('account_name')->nullable();    // name on the account
            $table->text('instructions')->nullable();      // extra notes shown to the tenant
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['landlord_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landlord_payout_accounts');
    }
};
