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
        Schema::create('stock_in_edit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_in_id')->constrained('stock_ins')->cascadeOnDelete();
            $table->foreignId('old_item_id')->constrained('items');
            $table->foreignId('new_item_id')->constrained('items');
            $table->unsignedInteger('old_jumlah');
            $table->unsignedInteger('new_jumlah');
            $table->date('old_tanggal');
            $table->date('new_tanggal');
            $table->string('old_sumber')->nullable();
            $table->string('new_sumber')->nullable();
            $table->text('old_keterangan')->nullable();
            $table->text('new_keterangan')->nullable();
            $table->foreignId('requested_by')->constrained('users');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->text('catatan_approval')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_in_edit_requests');
    }
};
