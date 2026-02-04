<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reader_id');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reader_id')->references('id')->on('readers')->onDelete('cascade');

            $table->unique(['user_id'], 'uniq_res_cart_user');
            $table->unique(['reader_id'], 'uniq_res_cart_reader');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_carts');
    }
};
