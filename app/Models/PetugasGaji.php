<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PetugasGaji extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'petugas',
        'bulan',
        'tahun',
        'nominal',
    ];

    protected function casts(): array
    {
        return [
            'bulan' => 'integer',
            'tahun' => 'integer',
            'nominal' => 'integer',
        ];
    }
}
