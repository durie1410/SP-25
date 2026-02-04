<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_delete_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->unsignedBigInteger('requested_by');
            $table->unsignedBigInteger('approved_by')->nullable();

            $table->string('status')->default('pending'); // pending|approved|rejected
            $table->text('reason')->nullable();
            $table->text('admin_note')->nullable();

            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            $table->timestamps();

            $table->index(['book_id', 'status']);
            $table->index(['requested_by', 'status']);

            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_delete_requests');
    }
};
