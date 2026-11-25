<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['kunci', 'nilai', 'tipe', 'grup'])]
class KonfigurasiSistem extends Model
{
    protected $table = 'konfigurasi_sistem';
}
