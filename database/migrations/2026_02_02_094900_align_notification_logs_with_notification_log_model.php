<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('notification_logs', 'body')) {
                $table->text('body')->nullable()->after('subject');
            }
            if (!Schema::hasColumn('notification_logs', 'priority')) {
                $table->string('priority', 20)->default('normal')->after('body');
            }
            if (!Schema::hasColumn('notification_logs', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('sent_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('notification_logs', function (Blueprint $table) {
            if (Schema::hasColumn('notification_logs', 'read_at')) {
                $table->dropColumn('read_at');
            }
            if (Schema::hasColumn('notification_logs', 'priority')) {
                $table->dropColumn('priority');
            }
            if (Schema::hasColumn('notification_logs', 'body')) {
                $table->dropColumn('body');
            }
        });
    }
};
