<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['unit_peralatan_id', 'teknisi_id', 'judul', 'deskripsi', 'biaya', 'status', 'tanggal_mulai', 'tanggal_selesai'])]
class ServisUnit extends Model
{
    protected $table = 'servis_unit';

    protected function casts(): array
    {
        return [
            'biaya' => 'decimal:2',
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(UnitPeralatan::class, 'unit_peralatan_id');
    }

    public function teknisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }
}
