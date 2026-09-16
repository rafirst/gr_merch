<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 'cabang_id', 'jumlah', 'tanggal', 'sumber', 'keterangan', 'user_id',
    ];

    protected $casts = ['tanggal' => 'date'];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
