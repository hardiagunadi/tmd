<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OtherTransaction extends Model
{
    protected $fillable = [
        'tanggal',
        'jenis',
        'kategori',
        'pihak',
        'deskripsi',
        'nominal',
    ];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'nominal' => 'integer',
        ];
    }
}
