<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_cart_items', function (Blueprint $table) {
            $table->integer('days')->default(1)->after('book_id')->comment('Số ngày mượn');
            $table->decimal('daily_fee', 10, 2)->default(5000)->after('days')->comment('Phí hàng ngày (5000/ngày)');
        });
    }

    public function down(): void
    {
        Schema::table('reservation_cart_items', function (Blueprint $table) {
            $table->dropColumn(['days', 'daily_fee']);
        });
    }
};
