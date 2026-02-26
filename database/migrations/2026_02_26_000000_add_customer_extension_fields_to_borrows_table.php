<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->boolean('customer_extension_requested')
                ->default(false)
                ->after('customer_rejection_reason');

            $table->unsignedInteger('customer_extension_days')
                ->nullable()
                ->after('customer_extension_requested');

            $table->timestamp('customer_extension_requested_at')
                ->nullable()
                ->after('customer_extension_days');
        });
    }

    public function down(): void
    {
        Schema::table('borrows', function (Blueprint $table) {
            $table->dropColumn([
                'customer_extension_requested',
                'customer_extension_days',
                'customer_extension_requested_at',
            ]);
        });
    }
};

