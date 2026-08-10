<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('address', 500)->nullable()->after('unit_id');
        });

        DB::table('expenses')
            ->whereNull('address')
            ->whereNotNull('property_id')
            ->orderBy('id')
            ->eachById(function (object $expense): void {
                $property = DB::table('properties')->where('id', $expense->property_id)->first();

                if (! $property) {
                    return;
                }

                $address = collect([
                    $property->address_line_1,
                    $property->address_line_2,
                    $property->city,
                    $property->region,
                    $property->postal_code,
                    $property->country,
                ])->filter()->implode(', ');

                DB::table('expenses')->where('id', $expense->id)->update([
                    'address' => $address ?: null,
                ]);
            });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('address');
        });
    }
};
