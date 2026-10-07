<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->string('nomor_telepon', 30)->nullable()->after('nomor_spk');
            $table->string('pic_penjualan', 255)->nullable()->after('nama_customer');
            $table->enum('jenis', ['penjualan', 'DO', 'request'])->default('penjualan')->change();
        });
    }

    public function down(): void
    {
        Schema::table('stock_outs', function (Blueprint $table) {
            $table->enum('jenis', ['penjualan', 'hadiah', 'request'])->default('penjualan')->change();
            $table->dropColumn(['nomor_telepon', 'pic_penjualan']);
        });
    }
};
