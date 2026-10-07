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
        Schema::table('stock_outs', function (Blueprint $table): void {
            $table->string('kode_pembayaran', 100)->nullable()->after('approved_at');
            $table->string('bukti_pembayaran')->nullable()->after('kode_pembayaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_outs', function (Blueprint $table): void {
            $table->dropColumn(['kode_pembayaran', 'bukti_pembayaran']);
        });
    }
};
