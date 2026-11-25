<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['peralatan_id', 'kode_unit', 'status', 'kondisi', 'catatan', 'tanggal_pengadaan'])]
class UnitPeralatan extends Model
{
    protected $table = 'unit_peralatan';

    protected function casts(): array
    {
        return ['tanggal_pengadaan' => 'date'];
    }

    public function peralatan(): BelongsTo
    {
        return $this->belongsTo(Peralatan::class);
    }

    public function itemSewa(): HasMany
    {
        return $this->hasMany(ItemSewa::class);
    }

    public function servis(): HasMany
    {
        return $this->hasMany(ServisUnit::class, 'unit_peralatan_id');
    }
}
