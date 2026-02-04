<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservation_cart_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('book_id');
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on('reservation_carts')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');

            $table->unique(['cart_id', 'book_id'], 'uniq_res_cart_item');
            $table->index(['book_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservation_cart_items');
    }
};
