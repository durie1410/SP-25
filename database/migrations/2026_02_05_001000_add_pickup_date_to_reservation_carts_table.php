<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservation_carts', function (Blueprint $table) {
            $table->date('pickup_date')->nullable()->after('reader_id')->comment('Ngày dự kiến lấy sách (tối thiểu 3 ngày)');
        });
    }

    public function down(): void
    {
        Schema::table('reservation_carts', function (Blueprint $table) {
            $table->dropColumn('pickup_date');
        });
    }
};
