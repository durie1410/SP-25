<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_items', function (Blueprint $table) {
            if (!Schema::hasColumn('borrow_items', 'added_in_payment')) {
                $table->boolean('added_in_payment')
                    ->default(false)
                    ->after('borrow_type')
                    ->comment('Danh dau sach duoc them o buoc thanh toan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('borrow_items', function (Blueprint $table) {
            if (Schema::hasColumn('borrow_items', 'added_in_payment')) {
                $table->dropColumn('added_in_payment');
            }
        });
    }
};
