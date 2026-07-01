<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rent_invoices', function (Blueprint $table) {
            $table->boolean('is_auto_generated')->default(false)->after('notes');
            $table->timestamp('sent_at')->nullable()->after('is_auto_generated');
            $table->json('reminders_sent')->nullable()->after('sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('rent_invoices', function (Blueprint $table) {
            $table->dropColumn(['is_auto_generated', 'sent_at', 'reminders_sent']);
        });
    }
};
