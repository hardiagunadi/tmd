<?php
// app/Http/Controllers/TagihanController.php
namespace App\Http\Controllers;

use App\Imports\TagihanRawImport;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class TagihanController extends Controller
{
    // daftar + filter bulan
    public function index(Request $request)
    {
        $bulan = $request->get('bulan');          // 1–12 atau null
        $tahun = $request->get('tahun', now()->year);

        $query = Tagihan::query()->where('tahun_tagihan', $tahun);

        if ($bulan) {
            $query->where('bulan_tagihan', $bulan);
        }

        $tagihans = $query->orderBy('nama_instansi')->paginate(20);

        return view('tagihan.index', compact('tagihans', 'bulan', 'tahun'));
    }

 public function importForm()
{
    $bulanSekarang = now()->month;
    $tahunSekarang = now()->year;

    return view('tagihan.import', compact('bulanSekarang', 'tahunSekarang'));
}

    // STEP 1: preview data
  public function importPreview(Request $request)
{
    $request->validate([
        'file'          => 'required|file|mimes:xlsx,xls',
        'bulan_tagihan' => 'required|integer|min:1|max:12',
        'tahun_tagihan' => 'required|integer|min:2000',
    ]);

    $bulan = (int) $request->bulan_tagihan;
    $tahun = (int) $request->tahun_tagihan;

    $import = new TagihanRawImport();
    Excel::import($import, $request->file('file'));

    $rows = $import->rows ?? collect();

    // simpan ke session untuk step simpan
    Session::put('tagihan_import_rows', $rows->values()->toArray());
    Session::put('tagihan_import_meta', [
        'bulan_tagihan' => $bulan,
        'tahun_tagihan' => $tahun,
    ]);

    return view('tagihan.import_preview', compact('rows', 'bulan', 'tahun'));
}

    // STEP 2: konfirmasi simpan
public function importStore(Request $request)
{
    // meta bulan & tahun tetap dari session
    $meta = Session::get('tagihan_import_meta');

    if (!$meta) {
        return redirect()->route('tagihan.import.form')
            ->with('error', 'Session import kosong, silakan upload ulang.');
    }

    // data hasil edit dari form preview
    $rowsInput = $request->input('rows', []);
    $selected  = $request->input('selected_rows', []);

    if (empty($rowsInput) || empty($selected)) {
        return back()->with('error', 'Tidak ada data yang dipilih untuk di-import.');
    }

    $bulan = (int) $meta['bulan_tagihan'];
    $tahun = (int) $meta['tahun_tagihan'];

    foreach ($selected as $index) {
        if (!isset($rowsInput[$index])) {
            continue;
        }

        $row = $rowsInput[$index];

        if (empty($row['invoice'])) {
            continue;
        }

        $deskripsiPaket = trim(
            ($row['paket_langganan'] ?? '') . ' ' .
            ($row['tipe_service'] ?? '')
        );

        // bersihkan angka total (kalau nanti Anda pakai format dengan titik/koma)
        $total = isset($row['total']) ? (int) preg_replace('/[^\d]/', '', $row['total']) : 0;

        Tagihan::updateOrCreate(
            ['no_invoice' => $row['invoice']],
            [
                'nama_instansi'   => $row['nama'] ?? '',
                'alamat_instansi' => $row['alamat'] ?? '',
                'no_pelanggan'    => (string)($row['id_pelanggan'] ?? ''),
                'bulan_tagihan'   => $bulan,
                'tahun_tagihan'   => $tahun,
                'biaya_langganan' => $total,
                'biaya_admin'     => 0,
                'deskripsi_paket' => $deskripsiPaket ?: 'High Speed Internet Package Service',
            ]
        );
    }

    // meta masih boleh kita hapus, rows sudah dari request
    Session::forget('tagihan_import_rows');
    Session::forget('tagihan_import_meta');

    return redirect()->route('tagihan.index')
        ->with('success', 'Data tagihan terpilih berhasil disimpan.');
}

   // app/Http/Controllers/TagihanController.php

public function print(Tagihan $tagihan)
{
    // tandai sebagai sudah cetak (kalau mau, bisa pakai kondisi kalau masih null saja)
    if (is_null($tagihan->printed_at)) {
        $tagihan->update(['printed_at' => now()]);
    }

    return view('tagihan.print', compact('tagihan'));
}
public function printBatch(Request $request)
{
    $request->validate([
        'jumlah'        => 'required|in:10,15,20',
        'bulan'         => 'nullable|integer|min:1|max:12',
        'tahun'         => 'nullable|integer',
        'hanya_belum'   => 'nullable|boolean', // optional: cetak hanya yang belum cetak
    ]);

    $jumlah = (int) $request->jumlah;
    $bulan  = $request->bulan;
    $tahun  = $request->tahun ?? now()->year;

    $query = Tagihan::query()->where('tahun_tagihan', $tahun);

    if ($bulan) {
        $query->where('bulan_tagihan', $bulan);
    }

    // optional: kalau mau hanya yang belum cetak
    if ($request->boolean('hanya_belum', true)) {
        $query->whereNull('printed_at');
    }

    $tagihans = $query
        ->orderBy('nama_instansi')
        ->limit($jumlah)
        ->get();

    if ($tagihans->isEmpty()) {
        return back()->with('error', 'Tidak ada data yang bisa dicetak.');
    }

    // tandai semua sebagai sudah cetak
    foreach ($tagihans as $t) {
        if (is_null($t->printed_at)) {
            $t->printed_at = now();
            $t->save();
        }
    }

    // view khusus untuk cetak banyak nota sekaligus
    return view('tagihan.print_batch', compact('tagihans'));
}

}
