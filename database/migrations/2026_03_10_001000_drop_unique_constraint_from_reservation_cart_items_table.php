<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $indexes = collect(DB::select("SHOW INDEX FROM reservation_cart_items"))
            ->pluck('Key_name')
            ->unique()
            ->values();

        if ($indexes->contains('uniq_res_cart_item')) {
            Schema::table('reservation_cart_items', function (Blueprint $table) {
                $table->dropUnique('uniq_res_cart_item');
            });
        }
    }

    public function down(): void
    {
        $indexes = collect(DB::select("SHOW INDEX FROM reservation_cart_items"))
            ->pluck('Key_name')
            ->unique()
            ->values();

        if (!$indexes->contains('uniq_res_cart_item')) {
            Schema::table('reservation_cart_items', function (Blueprint $table) {
                $table->unique(['cart_id', 'book_id'], 'uniq_res_cart_item');
            });
        }
    }
};