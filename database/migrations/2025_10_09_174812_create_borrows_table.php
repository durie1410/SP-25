<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBorrowsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('borrows', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reader_id');
            $table->string('borrow_code')->nullable()->index();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('librarian_id')->nullable(); // Thủ thư cho mượn
            $table->date('ngay_muon');
            $table->date('ngay_hen_tra');
            $table->date('ngay_tra_thuc_te')->nullable();
            $table->enum('trang_thai', ['Dang muon', 'Da tra', 'Qua han', 'Mat sach'])->default('Dang muon');
            $table->integer('so_lan_gia_han')->default(0);
            $table->date('ngay_gia_han_cuoi')->nullable();
            $table->text('ghi_chu')->nullable();
            $table->timestamps();

            $table->foreign('reader_id')->references('id')->on('readers')->onDelete('cascade');
            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('librarian_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('borrows');
    }
}
