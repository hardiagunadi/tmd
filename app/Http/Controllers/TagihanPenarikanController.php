<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\TagihanPenarikan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TagihanPenarikanController extends Controller
{
    private const PETUGAS_LIST = ['Slamet', 'Deswi', 'Ade', 'Hardi'];

    public function index(Request $request): View
    {
        $tahun = $request->integer('tahun') ?? $this->defaultTahun();
        $bulan = $request->integer('bulan') ?? $this->defaultBulan($tahun);

        $tagihans = $this->tagihansByPeriode($bulan, $tahun);

        $penarikans = TagihanPenarikan::query()
            ->with('tagihan')
            ->when($tahun, function ($query) use ($tahun) {
                $query->whereHas('tagihan', function ($sub) use ($tahun) {
                    $sub->where('tahun_tagihan', $tahun);
                });
            })
            ->when($bulan, function ($query) use ($bulan) {
                $query->whereHas('tagihan', function ($sub) use ($bulan) {
                    $sub->where('bulan_tagihan', $bulan);
                });
            })
            ->orderBy('petugas')
            ->orderBy('nama_pelanggan')
            ->get();

        return view('tagihan.penarikan', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tagihans' => $tagihans,
            'penarikans' => $penarikans,
            'petugasList' => self::PETUGAS_LIST,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'petugas' => 'required|in:'.implode(',', self::PETUGAS_LIST),
            'tagihan_id' => 'required|integer|exists:tagihans,id',
            'nominal' => 'nullable|integer|min:0',
        ]);

        $tagihan = Tagihan::findOrFail($validated['tagihan_id']);

        $nominal = $validated['nominal'] ?? $tagihan->total_bayar;

        TagihanPenarikan::updateOrCreate(
            ['tagihan_id' => $tagihan->id],
            [
                'petugas' => $validated['petugas'],
                'nama_pelanggan' => $tagihan->nama_instansi,
                'nominal' => $nominal,
            ]
        );

        return redirect()->route('tagihan.penarikan.index', [
            'bulan' => $tagihan->bulan_tagihan,
            'tahun' => $tagihan->tahun_tagihan,
        ])->with('success', 'Data penarikan berhasil disimpan untuk petugas terpilih.');
    }

    public function update(TagihanPenarikan $penarikan, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'petugas' => 'required|in:'.implode(',', self::PETUGAS_LIST),
            'nominal' => 'required|integer|min:0',
        ]);

        $penarikan->update($validated);

        $redirectParams = [];

        if ($penarikan->tagihan) {
            $redirectParams['bulan'] = $penarikan->tagihan->bulan_tagihan;
            $redirectParams['tahun'] = $penarikan->tagihan->tahun_tagihan;
        }

        return redirect()->route('tagihan.penarikan.index', $redirectParams)
            ->with('success', 'Data penarikan berhasil diperbarui.');
    }

    private function defaultTahun(): int
    {
        return Tagihan::query()->max('tahun_tagihan') ?? now()->year;
    }

    private function defaultBulan(int $tahun): ?int
    {
        return Tagihan::query()
            ->where('tahun_tagihan', $tahun)
            ->max('bulan_tagihan');
    }

    private function tagihansByPeriode(?int $bulan, ?int $tahun): Collection
    {
        return Tagihan::query()
            ->when($tahun, fn ($query) => $query->where('tahun_tagihan', $tahun))
            ->when($bulan, fn ($query) => $query->where('bulan_tagihan', $bulan))
            ->orderBy('nama_instansi')
            ->get();
    }
}
