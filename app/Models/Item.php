<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Item extends Model
{
    use HasFactory;

    public static function kategoriOptions(): array
    {
        return ['jacket', 't-shirt', 'shirt', 'tumbler', 'umbrella', 'topi'];
    }

    protected $fillable = [
        'kode_items', 'nama_items', 'kategori', 'harga_items',
        'stok_items', 'harga_jual', 'foto', 'cabang_id',
    ];

    protected $casts = [
        'harga_items' => 'decimal:2',
        'harga_jual' => 'decimal:2',
    ];

    public function cabang()
    {
        return $this->belongsTo(Cabang::class);
    }

    public function stockIns()
    {
        return $this->hasMany(StockIn::class);
    }

    public function latestStockIn(): HasOne
    {
        return $this->hasOne(StockIn::class)->latestOfMany();
    }

    public function stockOuts()
    {
        return $this->hasMany(StockOut::class);
    }

    public function isStokMenipis(): bool
    {
        return $this->stok_items <= 5; // Assuming a default minimum stock of 5
    }
}
