<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Stripe customer ID so we never create duplicate customers
        Schema::table('users', function (Blueprint $table) {
            $table->string('stripe_customer_id')->nullable()->after('remember_token')->index();
        });

        // Each plan maps to a Stripe Price (run stripe:sync-plans after seeding)
        Schema::table('plans', function (Blueprint $table) {
            $table->string('stripe_price_id')->nullable()->after('is_active');
        });

        // Track the active gateway subscription and its raw status
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('gateway')->nullable()->after('payment_method')
                ->comment('stripe|waafi|cacbank|manual');
            $table->string('gateway_subscription_id')->nullable()->after('gateway')
                ->comment('Stripe subscription ID or operator transaction reference');
            $table->string('gateway_status')->nullable()->after('gateway_subscription_id')
                ->comment('Raw status string from the gateway');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $t) => $t->dropColumn('stripe_customer_id'));
        Schema::table('plans', fn (Blueprint $t) => $t->dropColumn('stripe_price_id'));
        Schema::table('subscriptions', fn (Blueprint $t) => $t->dropColumns([
            'gateway', 'gateway_subscription_id', 'gateway_status',
        ]));
    }
};
