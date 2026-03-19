<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReservationIdToBorrowItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('borrow_items', function (Blueprint $table) {
            $table->unsignedBigInteger('reservation_id')->nullable()->after('borrow_id');
            $table->foreign('reservation_id')->references('id')->on('inventory_reservations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('borrow_items', function (Blueprint $table) {
            //
        });
    }
}
