<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TagihanPenarikan extends Model
{
    protected $fillable = [
        'tagihan_id',
        'nama_pelanggan',
        'petugas',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'integer',
    ];

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }
}
