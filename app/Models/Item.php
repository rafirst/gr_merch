<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    public static function kategoriOptions(): array
    {
        return ['jacket', 'cap', 't-shirt', 'shirt', 'tumbler', 'umbrella'];
    }

    protected $fillable = [
        'kode_items', 'nama_items', 'kategori', 'harga_items',
        'stok_items', 'stok_minimum', 'satuan', 'foto', 'cabang_id',
    ];

    protected $casts = [
        'harga_items' => 'decimal:2',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    public function stockOuts()
    {
        return $this->hasMany(StockOut::class);
    }

    public function isStokMenipis(): bool
    {
        return $this->stok_items <= $this->stok_minimum;
    }
}
