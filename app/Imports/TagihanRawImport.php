<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class TagihanRawImport implements ToCollection, WithHeadingRow
{
    public Collection $rows;

    // HEADER ADA DI BARIS 5 (Invoice, ID Pelanggan, dst)
    public function headingRow(): int
    {
        return 5;
    }

    public function collection(Collection $rows)
    {
        // sekarang $rows sudah punya key:
        // 'invoice', 'id_pelanggan', 'nama', 'nomor_hp', 'alamat',
        // 'paket_langganan', 'tipe_service', 'biaya_internet',
        // 'biaya_instalasi', 'sewa_perangkat', 'fee_seller',
        // 'ppn', 'total', 'jatuh_tempo'
        $this->rows = $rows;
    }
}
