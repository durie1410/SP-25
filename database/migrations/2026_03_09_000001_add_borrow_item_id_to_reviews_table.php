<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('reviews', 'borrow_item_id')) {
                $table->unsignedBigInteger('borrow_item_id')->nullable()->after('user_id');
                $table->foreign('borrow_item_id')->references('id')->on('borrow_items')->onDelete('cascade');
            }
        });

        Schema::table('reviews', function (Blueprint $table) {
            try {
                $table->dropUnique(['book_id', 'user_id']);
            } catch (\Throwable $e) {
                // Ignore if unique key has already been removed or uses another name.
            }

            $table->unique('borrow_item_id', 'reviews_borrow_item_id_unique');
            $table->index(['book_id', 'user_id'], 'reviews_book_user_index');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            try {
                $table->dropUnique('reviews_borrow_item_id_unique');
            } catch (\Throwable $e) {
                // Ignore missing index.
            }

            try {
                $table->dropIndex('reviews_book_user_index');
            } catch (\Throwable $e) {
                // Ignore missing index.
            }

            if (Schema::hasColumn('reviews', 'borrow_item_id')) {
                $table->dropForeign(['borrow_item_id']);
                $table->dropColumn('borrow_item_id');
            }

            $table->unique(['book_id', 'user_id']);
        });
    }
};
