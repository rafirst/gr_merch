<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 'cabang_id', 'jumlah', 'jenis', 'harga_jual', 'total',
        'status', 'tanggal', 'keterangan', 'user_id', 'approved_by',
        'approved_at', 'catatan_approval',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'approved_at' => 'datetime',
    ];

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

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // Penjualan langsung approved otomatis; hadiah & request butuh approval admin pusat
    public function butuhApproval(): bool
    {
        return in_array($this->jenis, ['hadiah', 'request']);
    }
}
