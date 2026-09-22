<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockInEditRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_in_id', 'old_item_id', 'new_item_id', 'old_jumlah', 'new_jumlah',
        'old_tanggal', 'new_tanggal', 'old_sumber', 'new_sumber', 'old_keterangan',
        'new_keterangan', 'requested_by', 'status', 'approved_by', 'approved_at',
        'catatan_approval',
    ];

    protected $casts = [
        'old_tanggal' => 'date',
        'new_tanggal' => 'date',
        'approved_at' => 'datetime',
    ];

    public function stockIn()
    {
        return $this->belongsTo(StockIn::class);
    }

    public function oldItem()
    {
        return $this->belongsTo(Item::class, 'old_item_id');
    }

    public function newItem()
    {
        return $this->belongsTo(Item::class, 'new_item_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
