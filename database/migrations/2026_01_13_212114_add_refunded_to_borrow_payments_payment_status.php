<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddRefundedToBorrowPaymentsPaymentStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE borrow_payments MODIFY COLUMN payment_status ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE borrow_payments MODIFY COLUMN payment_status ENUM('pending', 'success', 'failed') DEFAULT 'pending'");
    }
}
