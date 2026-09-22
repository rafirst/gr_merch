<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('kode_items');
            $table->string('nama_items');
            $table->enum('kategori', ['jacket', 't-shirt', 'shirt', 'tumbler', 'umbrella', 'topi'])->nullable();
            $table->decimal('harga_items', 15, 2)->default(0);
            $table->integer('stok_items')->default(0);
            $table->decimal('harga_jual', 15, 2)->default(0);
            $table->string('foto')->nullable();
            $table->foreignId('cabang_id')->constrained('cabangs')->cascadeOnDelete();
            $table->timestamps();

            // kode_items unik per cabang (item yang sama bisa ada di beberapa cabang, masing2 stok sendiri)
            $table->unique(['kode_items', 'cabang_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
