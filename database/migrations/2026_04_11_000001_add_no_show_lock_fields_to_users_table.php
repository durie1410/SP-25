<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('locked_at');
            }

            if (!Schema::hasColumn('users', 'locked_reason')) {
                $table->text('locked_reason')->nullable()->after('is_locked');
            }

            if (!Schema::hasColumn('users', 'no_show_count')) {
                $table->unsignedInteger('no_show_count')->default(0)->after('locked_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'no_show_count')) {
                $table->dropColumn('no_show_count');
            }

            if (Schema::hasColumn('users', 'locked_reason')) {
                $table->dropColumn('locked_reason');
            }

            if (Schema::hasColumn('users', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
        });
    }
};
