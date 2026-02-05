<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_cart_items', function (Blueprint $table) {
            $table->date('pickup_date')->nullable()->after('daily_fee')->comment('Ngày lấy sách');
            $table->date('return_date')->nullable()->after('pickup_date')->comment('Ngày trả sách');
        });
    }

    public function down(): void
    {
        Schema::table('reservation_cart_items', function (Blueprint $table) {
            $table->dropColumn(['pickup_date', 'return_date']);
        });
    }
};
