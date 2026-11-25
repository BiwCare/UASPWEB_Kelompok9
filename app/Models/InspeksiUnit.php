<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['unit_peralatan_id', 'transaksi_sewa_id', 'inspektur_id', 'jenis', 'kondisi', 'catatan', 'foto', 'diperiksa_pada'])]
class InspeksiUnit extends Model
{
    protected $table = 'inspeksi_unit';

    protected function casts(): array
    {
        return ['diperiksa_pada' => 'datetime'];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitPeralatan::class, 'unit_peralatan_id');
    }

    public function transaksiSewa(): BelongsTo
    {
        return $this->belongsTo(TransaksiSewa::class, 'transaksi_sewa_id');
    }
}
