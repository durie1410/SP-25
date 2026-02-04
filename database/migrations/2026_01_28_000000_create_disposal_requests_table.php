<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('disposal_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id')->nullable()->comment('Nếu thanh lý theo sách');
            $table->unsignedBigInteger('inventory_id')->nullable()->comment('Nếu thanh lý theo bản sao cụ thể');
            $table->unsignedBigInteger('requested_by')->comment('Librarian tạo yêu cầu');
            $table->string('reason', 500)->nullable();
            $table->enum('type', ['book', 'copy'])->default('book');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable()->comment('Admin duyệt sau này');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_note')->nullable();
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('inventory_id')->references('id')->on('inventories')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disposal_requests');
    }
};

