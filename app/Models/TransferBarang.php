<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransferBarang extends Model
{
    public const STATUS_DITERIMA = 'diterima';

    public const STATUS_PROSES = 'proses';

    public const STATUS_BATAL = 'batal';

    protected $fillable = [
        'from_cabang_id', 'to_cabang_id', 'created_by', 'received_by',
        'status', 'sent_at', 'received_at', 'bukti_foto',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'received_at' => 'datetime',
            'bukti_foto' => 'array',
        ];
    }

    public function fromCabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'from_cabang_id');
    }

    public function toCabang(): BelongsTo
    {
        return $this->belongsTo(Cabang::class, 'to_cabang_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransferBarangItem::class);
    }
}
