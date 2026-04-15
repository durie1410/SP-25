<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            if (!Schema::hasColumn('suppliers', 'status')) {
                $table->enum('status', ['active', 'inactive'])->default('active')->after('address');
            }
        });

        DB::table('suppliers')->whereNull('status')->update(['status' => 'active']);

        // Add unique index for supplier name when no duplicates exist in current data.
        $duplicates = DB::table('suppliers')
            ->selectRaw('LOWER(TRIM(name)) as normalized_name, COUNT(*) as c')
            ->groupBy('normalized_name')
            ->having('c', '>', 1)
            ->count();

        if ($duplicates === 0) {
            $indexExists = DB::table('information_schema.statistics')
                ->where('table_schema', DB::raw('DATABASE()'))
                ->where('table_name', 'suppliers')
                ->where('index_name', 'suppliers_name_unique')
                ->exists();

            if (!$indexExists) {
                Schema::table('suppliers', function (Blueprint $table) {
                    $table->unique('name', 'suppliers_name_unique');
                });
            }
        }
    }

    public function down(): void
    {
        $indexExists = DB::table('information_schema.statistics')
            ->where('table_schema', DB::raw('DATABASE()'))
            ->where('table_name', 'suppliers')
            ->where('index_name', 'suppliers_name_unique')
            ->exists();

        Schema::table('suppliers', function (Blueprint $table) use ($indexExists) {
            if ($indexExists) {
                $table->dropUnique('suppliers_name_unique');
            }

            if (Schema::hasColumn('suppliers', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
