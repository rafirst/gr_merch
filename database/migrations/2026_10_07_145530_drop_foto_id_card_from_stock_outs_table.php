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
        Schema::table('stock_outs', function (Blueprint $table) {
            if (Schema::hasColumn('stock_outs', 'foto_id_card')) {
                $table->dropColumn('foto_id_card');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            if (! Schema::hasColumn('stock_outs', 'foto_id_card')) {
                $table->string('foto_id_card')->nullable()->after('alamat_customer');
            }
        });
    }
};
