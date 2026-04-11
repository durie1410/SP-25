<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_delete_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('book_delete_requests', 'proof_images')) {
                $table->json('proof_images')->nullable()->after('reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('book_delete_requests', function (Blueprint $table) {
            if (Schema::hasColumn('book_delete_requests', 'proof_images')) {
                $table->dropColumn('proof_images');
            }
        });
    }
};
