<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['user_id', 'nomor_sewa', 'tanggal_mulai', 'tanggal_selesai', 'jumlah_hari', 'subtotal', 'total_deposit', 'denda', 'total', 'status', 'status_pembayaran', 'alamat_pengiriman', 'catatan'])]
class TransaksiSewa extends Model
{
    protected $table = 'transaksi_sewa';

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'subtotal' => 'decimal:2',
            'total_deposit' => 'decimal:2',
            'denda' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemSewa::class, 'transaksi_sewa_id');
    }

    public function pembayaran(): HasMany
    {
        return $this->hasMany(PembayaranSewa::class, 'transaksi_sewa_id');
    }

    public function pengembalian(): HasOne
    {
        return $this->hasOne(PengembalianSewa::class, 'transaksi_sewa_id');
    }

    public function riwayatStatus(): HasMany
    {
        return $this->hasMany(RiwayatStatusSewa::class, 'transaksi_sewa_id');
    }
}
