<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nama', 'slug', 'deskripsi', 'ikon', 'aktif'])]
class KategoriPeralatan extends Model
{
    protected $table = 'kategori_peralatan';

    protected function casts(): array
    {
        return ['aktif' => 'boolean'];
    }

    public function peralatan(): HasMany
    {
        return $this->hasMany(Peralatan::class, 'kategori_peralatan_id');
    }
}
