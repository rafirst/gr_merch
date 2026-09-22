<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->string('nomor_telepon', 30)->nullable()->after('nomor_spk');
            $table->string('pic_penjualan', 255)->nullable()->after('nama_customer');
        });

        DB::statement("ALTER TABLE stock_outs MODIFY jenis ENUM('penjualan', 'DO', 'request') NOT NULL DEFAULT 'penjualan'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE stock_outs MODIFY jenis ENUM('penjualan', 'hadiah', 'request') NOT NULL DEFAULT 'penjualan'");

        Schema::table('stock_outs', function (Blueprint $table) {
            $table->dropColumn(['nomor_telepon', 'pic_penjualan']);
        });
    }
};
