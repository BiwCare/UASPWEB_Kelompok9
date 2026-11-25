<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['transaksi_sewa_id', 'peralatan_id', 'unit_peralatan_id', 'jumlah', 'harga_harian', 'subtotal'])]
class ItemSewa extends Model
{
    protected $table = 'item_sewa';

    protected function casts(): array
    {
        return [
            'harga_harian' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function transaksiSewa(): BelongsTo
    {
        return $this->belongsTo(TransaksiSewa::class, 'transaksi_sewa_id');
    }

    public function peralatan(): BelongsTo
    {
        return $this->belongsTo(Peralatan::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitPeralatan::class, 'unit_peralatan_id');
    }
}
