<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeTrangThaiChiTietNullableInBorrowsTable extends Migration
{
    /**
     * Run the migrations.
     * Cho phép trang_thai_chi_tiet có thể là NULL để phân biệt đơn hàng chưa được duyệt
     *
     * @return void
     */
    public function up()
    {
        // Sử dụng DB statement để thay đổi cột từ NOT NULL sang NULL
        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE borrows 
            MODIFY COLUMN trang_thai_chi_tiet 
            VARCHAR(50) 
            DEFAULT NULL 
            NULL
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Khôi phục lại NOT NULL với default value
        \Illuminate\Support\Facades\DB::statement("
            ALTER TABLE borrows 
            MODIFY COLUMN trang_thai_chi_tiet 
            VARCHAR(50) 
            DEFAULT 'don_hang_moi' 
            NOT NULL
        ");
    }
}
