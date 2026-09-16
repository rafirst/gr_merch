<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_outs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->integer('jumlah');
            $table->enum('jenis', ['penjualan', 'hadiah', 'request'])->default('penjualan');
            $table->decimal('harga_jual', 15, 2)->nullable(); // hanya untuk jenis penjualan
            $table->decimal('total', 15, 2)->nullable(); // hanya untuk jenis penjualan
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->date('tanggal');
            $table->text('keterangan')->nullable();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // yang input/request
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_outs');
    }
};
