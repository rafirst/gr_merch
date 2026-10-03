<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('transfer_barangs')->where('status', 'pending')->update(['status' => 'proses']);
        DB::table('transfer_barangs')->where('status', 'received')->update(['status' => 'diterima']);

        Schema::table('transfer_barangs', function (Blueprint $table) {
            $table->enum('status', ['diterima', 'proses', 'batal'])->default('proses')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transfer_barangs', function (Blueprint $table) {
            $table->string('status')->default('pending')->change();
        });

        DB::table('transfer_barangs')->where('status', 'proses')->update(['status' => 'pending']);
        DB::table('transfer_barangs')->whereIn('status', ['diterima', 'batal'])->update(['status' => 'received']);
    }
};
