<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_delete_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('book_delete_requests', 'borrow_item_id')) {
                $table->unsignedBigInteger('borrow_item_id')->nullable()->after('inventory_id');
                $table->index('borrow_item_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('book_delete_requests', function (Blueprint $table) {
            if (Schema::hasColumn('book_delete_requests', 'borrow_item_id')) {
                $table->dropIndex(['borrow_item_id']);
                $table->dropColumn('borrow_item_id');
            }
        });
    }
};
