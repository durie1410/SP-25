<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Schema;

class CreateMomoTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('momo_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique(); // Unique identifier for the transaction
            $table->decimal('amount', 10, 2); // Transaction amount
            $table->string('currency')->default('USD'); // Currency type
            $table->string('status'); // Transaction status
            $table->timestamps(); // Created at and updated at timestamps
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('momo_transactions');
    }
}
