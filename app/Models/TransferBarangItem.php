<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransferBarangItem extends Model
{
    protected $fillable = [
        'transfer_barang_id', 'source_item_id', 'target_item_id',
        'jumlah_dikirim', 'jumlah_diterima', 'catatan_selisih',
    ];

    public function transferBarang(): BelongsTo
    {
        return $this->belongsTo(TransferBarang::class);
    }

    public function sourceItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'source_item_id');
    }

    public function targetItem(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'target_item_id');
    }
}
