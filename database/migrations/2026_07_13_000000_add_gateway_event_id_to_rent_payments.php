<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rent_payments', function (Blueprint $table) {
            $table->string('gateway_event_id')->nullable()->after('reference_number');
            $table->unique('gateway_event_id');
        });
    }

    public function down(): void
    {
        Schema::table('rent_payments', function (Blueprint $table) {
            $table->dropUnique(['gateway_event_id']);
            $table->dropColumn('gateway_event_id');
        });
    }
};
