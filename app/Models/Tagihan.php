<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    protected $fillable = [
        'nama_instansi',
        'alamat_instansi',
        'no_invoice',
        'no_pelanggan',
        'bulan_tagihan',
        'tahun_tagihan',
        'biaya_langganan',
        'biaya_admin',
        'deskripsi_paket',
        'printed_at', // <-- tambahkan        
    ];

    protected $casts = [
        'bulan_tagihan'   => 'integer',
        'tahun_tagihan'   => 'integer',
        'biaya_langganan' => 'integer',
        'biaya_admin'     => 'integer',
        'printed_at'      => 'datetime', // <-- tambahkan
    ];

    public function getStatusCetakAttribute(): string
    {
        return $this->printed_at ? 'Sudah cetak' : 'Belum cetak';
    }
    // Nama bulan: "Juni"
    public function getNamaBulanTagihanAttribute(): string
    {
        return Carbon::create($this->tahun_tagihan, $this->bulan_tagihan, 1)
            ->translatedFormat('F');
    }

    // Tgl jatuh tempo: tgl 10 bulan berikutnya
    public function getTanggalJatuhTempoAttribute(): Carbon
    {
        return Carbon::create($this->tahun_tagihan, $this->bulan_tagihan, 10)
            ->addMonth();
    }

    public function getTotalBayarAttribute(): int
    {
        return $this->biaya_langganan + $this->biaya_admin;
    }
}
