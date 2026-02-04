<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_reservations', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('inventory_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('reader_id');

            $table->enum('status', ['pending', 'ready', 'fulfilled', 'cancelled'])->default('pending');

            $table->text('notes')->nullable();
            $table->text('admin_note')->nullable();

            $table->unsignedBigInteger('processed_by')->nullable();

            $table->timestamp('ready_at')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();

            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('inventory_id')->references('id')->on('inventories')->nullOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reader_id')->references('id')->on('readers')->onDelete('cascade');
            $table->foreign('processed_by')->references('id')->on('users')->nullOnDelete();

            $table->index(['status', 'created_at']);
            $table->unique(['book_id', 'user_id'], 'uniq_inventory_res_book_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_reservations');
    }
};
