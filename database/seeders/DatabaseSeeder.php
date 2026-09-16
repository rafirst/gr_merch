<?php

namespace Database\Seeders;

use App\Models\Cabang;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Cabang contoh
        $pusat = Cabang::updateOrCreate(
            ['kode_cabang' => 'THO'],
            [
                'nama_cabang' => 'TAG Head Office',
                'alamat' => 'Kantor Pusat PT TAG Toyota',
            ]
        );

        // $cabangA = Cabang::create([
        //     'kode_cabang' => 'CBA',
        //     'nama_cabang' => 'Cabang A',
        // ]);

        // User Admin Pusat
        User::updateOrCreate(
            ['username' => 'administrator'],
            [
                'name' => 'Admin HO',
                'password' => Hash::make('password'),
                'role' => 'admin_ho',
                'cabang_id' => $pusat->id,
            ]
        );

        // User Staff Cabang
        // User::create([
        //     'name' => 'Staff Cabang A',
        //     'username' => 'staffcabang',
        //     'password' => Hash::make('password'),
        //     'role' => 'staff_cabang',
        //     'cabang_id' => $cabangA->id,
        // ]);
    }
}
