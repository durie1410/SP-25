<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_items', function (Blueprint $table) {
            if (!Schema::hasColumn('borrow_items', 'anh_bia_truoc')) {
                $table->string('anh_bia_truoc')->nullable()->after('ghi_chu');
            }

            if (!Schema::hasColumn('borrow_items', 'anh_bia_sau')) {
                $table->string('anh_bia_sau')->nullable()->after('anh_bia_truoc');
            }

            if (!Schema::hasColumn('borrow_items', 'anh_gay_sach')) {
                $table->string('anh_gay_sach')->nullable()->after('anh_bia_sau');
            }

            if (!Schema::hasColumn('borrow_items', 'ghi_chu_nhan_sach')) {
                $table->text('ghi_chu_nhan_sach')->nullable()->after('anh_gay_sach');
            }
        });
    }

    public function down(): void
    {
        Schema::table('borrow_items', function (Blueprint $table) {
            if (Schema::hasColumn('borrow_items', 'ghi_chu_nhan_sach')) {
                $table->dropColumn('ghi_chu_nhan_sach');
            }
            if (Schema::hasColumn('borrow_items', 'anh_gay_sach')) {
                $table->dropColumn('anh_gay_sach');
            }
            if (Schema::hasColumn('borrow_items', 'anh_bia_sau')) {
                $table->dropColumn('anh_bia_sau');
            }
            if (Schema::hasColumn('borrow_items', 'anh_bia_truoc')) {
                $table->dropColumn('anh_bia_truoc');
            }
        });
    }
};
