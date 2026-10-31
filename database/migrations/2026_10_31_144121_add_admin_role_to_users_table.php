<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin_ho', 'admin', 'staff_cabang') NOT NULL DEFAULT 'staff_cabang'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::table('users')->where('role', 'admin')->update(['role' => 'staff_cabang']);
            DB::statement("ALTER TABLE users MODIFY role ENUM('admin_ho', 'staff_cabang') NOT NULL DEFAULT 'staff_cabang'");
        }
    }
};
