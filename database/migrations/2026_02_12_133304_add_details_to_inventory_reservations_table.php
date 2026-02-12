<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailsToInventoryReservationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('inventory_reservations', function (Blueprint $table) {
            $table->date('pickup_date')->nullable()->after('reader_id');
            $table->date('return_date')->nullable()->after('pickup_date');
            $table->decimal('total_fee', 15, 2)->default(0)->after('return_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('inventory_reservations', function (Blueprint $table) {
            $table->dropColumn(['pickup_date', 'return_date', 'total_fee']);
        });
    }
}
