<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_items', function (Blueprint $table) {
            if (!Schema::hasColumn('borrow_items', 'borrow_type')) {
                $table->string('borrow_type', 20)->default('take_home')->after('trang_thai');
                $table->index('borrow_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('borrow_items', function (Blueprint $table) {
            if (Schema::hasColumn('borrow_items', 'borrow_type')) {
                $table->dropIndex(['borrow_type']);
                $table->dropColumn('borrow_type');
            }
        });
    }
};
