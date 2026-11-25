<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['transaksi_sewa_id', 'diproses_oleh', 'tanggal_kembali', 'kondisi', 'hari_terlambat', 'denda', 'status', 'catatan'])]
class PengembalianSewa extends Model
{
    protected $table = 'pengembalian_sewa';

    protected function casts(): array
    {
        return [
            'tanggal_kembali' => 'datetime',
            'denda' => 'decimal:2',
        ];
    }

    public function transaksiSewa(): BelongsTo
    {
        return $this->belongsTo(TransaksiSewa::class, 'transaksi_sewa_id');
    }

    public function diprosesOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'diproses_oleh');
    }
}
