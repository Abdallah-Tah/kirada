<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add residential unit types that were missing (house, villa, studio,
        // duplex, room). Kept the existing commercial types untouched.
        //
        // SQLite (used by the test suite) has no real ENUM type and no
        // MODIFY COLUMN — the column is already TEXT there, so this is a
        // no-op. Only MySQL needs the enum widened.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE units MODIFY COLUMN type ENUM(
            'apartment',
            'house',
            'villa',
            'studio',
            'duplex',
            'room',
            'office',
            'shop',
            'warehouse',
            'other'
        ) NOT NULL DEFAULT 'apartment'");
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Collapse any rows using the new types back to 'other' before
        // shrinking the enum, or MySQL will error / truncate to ''.
        DB::statement("UPDATE units SET type = 'other' WHERE type IN ('house','villa','studio','duplex','room')");

        DB::statement("ALTER TABLE units MODIFY COLUMN type ENUM(
            'apartment',
            'office',
            'shop',
            'warehouse',
            'other'
        ) NOT NULL DEFAULT 'apartment'");
    }
};
