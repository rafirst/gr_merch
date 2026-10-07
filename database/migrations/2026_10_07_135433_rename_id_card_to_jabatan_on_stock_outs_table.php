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
        if (Schema::hasColumn('stock_outs', 'id_card') && ! Schema::hasColumn('stock_outs', 'jabatan')) {
            Schema::table('stock_outs', function (Blueprint $table): void {
                $table->renameColumn('id_card', 'jabatan');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('stock_outs', 'jabatan') && ! Schema::hasColumn('stock_outs', 'id_card')) {
            Schema::table('stock_outs', function (Blueprint $table): void {
                $table->renameColumn('jabatan', 'id_card');
            });
        }
    }
};
