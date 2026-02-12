<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDetailedAddressToReadersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('readers', function (Blueprint $table) {
            $table->string('tinh_thanh')->nullable()->after('dia_chi');
            $table->string('huyen')->nullable()->after('tinh_thanh');
            $table->string('xa')->nullable()->after('huyen');
            $table->string('so_nha')->nullable()->after('xa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('readers', function (Blueprint $table) {
            $table->dropColumn(['tinh_thanh', 'huyen', 'xa', 'so_nha']);
        });
    }
}
