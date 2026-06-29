<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->string('category')->default('other')->after('description');
            $table->string('location')->nullable()->after('category');
            $table->boolean('permission_to_enter')->default(false)->after('location');
            $table->string('preferred_access_window')->nullable()->after('permission_to_enter');
        });

        Schema::create('maintenance_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('maintenance_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('maintenance_comment_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('disk')->default('private');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->string('kind')->default('initial');
            $table->boolean('is_internal')->default(false);
            $table->timestamps();

            $table->index(['maintenance_request_id', 'kind']);
            $table->index(['maintenance_comment_id', 'is_internal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_attachments');

        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn([
                'category',
                'location',
                'permission_to_enter',
                'preferred_access_window',
            ]);
        });
    }
};
