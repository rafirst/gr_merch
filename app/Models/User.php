<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'username', 'password', 'role', 'cabang_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function isAdminHo(): bool
    {
        return $this->role === 'admin_ho';
    }

    public function isStaffCabang(): bool
    {
        return $this->role === 'staff_cabang';
    }
}
