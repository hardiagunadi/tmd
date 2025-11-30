<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PendapatanLain extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'petugas',
        'keterangan',
        'pendapatan',
        'pengeluaran',
    ];

    protected function casts(): array
    {
        return [
            'pendapatan' => 'integer',
            'pengeluaran' => 'integer',
        ];
    }
}
