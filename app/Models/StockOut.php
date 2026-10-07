<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockOut extends Model
{
    use HasFactory;

    public const DISCOUNT_TAG_MEMBER = 15.0;

    public const DISCOUNT_RETAIL_KTP = 10.0;

    public const DISCOUNT_RETAIL_NON_KTP = 0.0;

    protected $fillable = [
        'item_id', 'cabang_id', 'jumlah', 'jenis', 'jenis_pembayaran', 'harga_jual', 'discount', 'paket_bundling', 'total',
        'nomor_spk', 'nomor_im', 'nomor_telepon', 'nama_customer', 'nik_ktp', 'jabatan', 'alamat_customer', 'pic_penjualan', 'batch_id', 'status', 'tanggal', 'keterangan', 'user_id', 'approved_by',
        'approved_at', 'catatan_approval', 'kode_pembayaran', 'bukti_pembayaran',
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

    public function discountOption(): ?string
    {
        if ($this->jenis !== 'penjualan') {
            return null;
        }

        return match ((float) $this->discount) {
            self::DISCOUNT_TAG_MEMBER => 'member',
            self::DISCOUNT_RETAIL_KTP => 'retail',
            self::DISCOUNT_RETAIL_NON_KTP => 'retail_non_ktp',
            default => null,
        };
    }

    public function discountLabel(): string
    {
        $discount = (float) ($this->discount ?? 0);

        if ($this->jenis === 'penjualan') {
            return match ($discount) {
                self::DISCOUNT_TAG_MEMBER => 'TAG Member (15%)',
                self::DISCOUNT_RETAIL_KTP => 'Retail - KTP (10%)',
                self::DISCOUNT_RETAIL_NON_KTP => 'Retail - Non KTP',
                default => rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.').'%',
            };
        }

        return rtrim(rtrim(number_format($discount, 2, '.', ''), '0'), '.').'%';
    }

    public function isTagMemberSale(): bool
    {
        return $this->jenis === 'penjualan'
            && (float) $this->discount === self::DISCOUNT_TAG_MEMBER;
    }

    public function isRetailNonKtpSale(): bool
    {
        return $this->jenis === 'penjualan'
            && (float) $this->discount === self::DISCOUNT_RETAIL_NON_KTP;
    }

    public function requiresNikKtp(): bool
    {
        return $this->jenis === 'penjualan'
            && (float) $this->discount === self::DISCOUNT_RETAIL_KTP;
    }

    // Request dan penjualan TAG Member membutuhkan approval admin pusat.
    public function butuhApproval(): bool
    {
        return $this->jenis === 'request'
            || $this->isTagMemberSale();
    }
}
