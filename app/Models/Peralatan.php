<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['kategori_peralatan_id', 'nama', 'slug', 'merek', 'deskripsi', 'spesifikasi', 'harga_per_hari', 'deposit', 'gambar', 'unggulan', 'aktif'])]
class Peralatan extends Model
{
    protected $table = 'peralatan';

    protected function casts(): array
    {
        return [
            'spesifikasi' => 'array',
            'harga_per_hari' => 'decimal:2',
            'deposit' => 'decimal:2',
            'unggulan' => 'boolean',
            'aktif' => 'boolean',
        ];
    }

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriPeralatan::class, 'kategori_peralatan_id');
    }

    public function units(): HasMany
    {
        return $this->hasMany(UnitPeralatan::class);
    }

    public function itemSewa(): HasMany
    {
        return $this->hasMany(ItemSewa::class);
    }
}
