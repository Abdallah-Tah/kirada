<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('stripe_id')->nullable()->index();
            $table->string('pm_type')->nullable();
            $table->string('pm_last_four', 4)->nullable();
            $table->timestamp('trial_ends_at')->nullable();
        });

        DB::table('users')
            ->whereNotNull('stripe_customer_id')
            ->update(['stripe_id' => DB::raw('stripe_customer_id')]);

        Schema::table('subscriptions', function (Blueprint $table): void {
            // Nullable for Kirada trial records created before Stripe checkout.
            $table->string('type')->nullable();
            $table->string('stripe_id')->nullable()->unique();
            $table->string('stripe_status')->nullable();
            $table->string('stripe_price')->nullable();
            $table->integer('quantity')->nullable();

            $table->index(['user_id', 'stripe_status']);
        });

        Schema::create('subscription_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('subscription_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_id')->unique();
            $table->string('stripe_product');
            $table->string('stripe_price');
            $table->string('meter_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->string('meter_event_name')->nullable();
            $table->timestamps();

            $table->index(['subscription_id', 'stripe_price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_items');

        Schema::table('subscriptions', function (Blueprint $table): void {
            $table->dropIndex(['user_id', 'stripe_status']);
            $table->dropUnique(['stripe_id']);
            $table->dropColumn(['type', 'stripe_id', 'stripe_status', 'stripe_price', 'quantity']);
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropIndex(['stripe_id']);
            $table->dropColumn(['stripe_id', 'pm_type', 'pm_last_four', 'trial_ends_at']);
        });
    }
};
