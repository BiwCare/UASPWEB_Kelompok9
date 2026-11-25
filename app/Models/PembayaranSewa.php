<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['transaksi_sewa_id', 'metode', 'jumlah', 'referensi', 'bukti_pembayaran', 'status', 'dibayar_pada', 'catatan'])]
class PembayaranSewa extends Model
{
    protected $table = 'pembayaran_sewa';

    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'dibayar_pada' => 'datetime',
        ];
    }

    public function transaksiSewa(): BelongsTo
    {
        return $this->belongsTo(TransaksiSewa::class, 'transaksi_sewa_id');
    }
}
