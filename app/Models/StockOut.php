<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id', 'cabang_id', 'jumlah', 'jenis', 'harga_jual', 'discount', 'voucher', 'total',
        'nomor_spk', 'nomor_telepon', 'nama_customer', 'pic_penjualan', 'batch_id', 'status', 'tanggal', 'keterangan', 'user_id', 'approved_by',
        'approved_at', 'catatan_approval',
    ];

    protected $casts = [
        'harga_jual' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
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

    // Hanya Request yang membutuhkan approval admin pusat.
    public function butuhApproval(): bool
    {
        return $this->jenis === 'request';
    }
}
