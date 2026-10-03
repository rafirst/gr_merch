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
        Schema::create('transfer_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_cabang_id')->constrained('cabangs')->restrictOnDelete();
            $table->foreignId('to_cabang_id')->constrained('cabangs')->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status')->default('pending')->index();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();
        });

        Schema::create('transfer_barang_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_barang_id')->constrained()->cascadeOnDelete();
            $table->foreignId('source_item_id')->constrained('items')->restrictOnDelete();
            $table->foreignId('target_item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->unsignedInteger('jumlah_dikirim');
            $table->unsignedInteger('jumlah_diterima')->nullable();
            $table->text('catatan_selisih')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfer_barang_items');
        Schema::dropIfExists('transfer_barangs');
    }
};
