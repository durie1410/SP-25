<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_cart_items', function (Blueprint $table) {
            $table->integer('quantity')->default(1)->after('return_date')->comment('Số lượng sách cần đặt');
        });
    }

    public function down(): void
    {
        Schema::table('reservation_cart_items', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
