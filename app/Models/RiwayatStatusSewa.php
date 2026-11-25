<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['transaksi_sewa_id', 'user_id', 'status', 'catatan'])]
class RiwayatStatusSewa extends Model
{
    protected $table = 'riwayat_status_sewa';

    public function transaksiSewa(): BelongsTo
    {
        return $this->belongsTo(TransaksiSewa::class, 'transaksi_sewa_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
