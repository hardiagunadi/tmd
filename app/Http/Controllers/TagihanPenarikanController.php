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
        $selectedPetugas = old('petugas', $request->session()->get('selected_penarikan_petugas'));

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

        $penarikanByPetugas = $penarikans->groupBy('petugas');
        $totalPerPetugas = collect(self::PETUGAS_LIST)
            ->mapWithKeys(function (string $petugas) use ($penarikanByPetugas): array {
                return [$petugas => $penarikanByPetugas->get($petugas, collect())->sum('nominal')];
            });
        $totalKeseluruhan = $totalPerPetugas->sum();

        $tagihanSudahDiambil = $penarikans
            ->pluck('tagihan_id')
            ->filter()
            ->all();

        $tagihansUntukInput = $this->tagihansByPeriode($bulan, $tahun, $tagihanSudahDiambil);

        return view('tagihan.penarikan', [
            'bulan' => $bulan,
            'tahun' => $tahun,
            'tagihans' => $tagihans,
            'tagihansUntukInput' => $tagihansUntukInput,
            'penarikans' => $penarikans,
            'penarikanByPetugas' => $penarikanByPetugas,
            'petugasList' => self::PETUGAS_LIST,
            'totalPerPetugas' => $totalPerPetugas,
            'totalKeseluruhan' => $totalKeseluruhan,
            'selectedPetugas' => $selectedPetugas,
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

        $existingPenarikan = TagihanPenarikan::query()
            ->where('tagihan_id', $tagihan->id)
            ->first();

        if ($existingPenarikan) {
            return redirect()
                ->route('tagihan.penarikan.index', [
                    'bulan' => $tagihan->bulan_tagihan,
                    'tahun' => $tagihan->tahun_tagihan,
                ])
                ->with('selected_penarikan_petugas', $validated['petugas'])
                ->with('error', 'Tagihan ini sudah tercatat pada petugas lain. Ubah data melalui daftar penarikan jika ingin memindahkan.');
        }

        $nominal = $validated['nominal'] ?? $tagihan->total_bayar;

        TagihanPenarikan::create([
            'tagihan_id' => $tagihan->id,
            'petugas' => $validated['petugas'],
            'nama_pelanggan' => $tagihan->nama_instansi,
            'nominal' => $nominal,
        ]);

        return redirect()->route('tagihan.penarikan.index', [
            'bulan' => $tagihan->bulan_tagihan,
            'tahun' => $tagihan->tahun_tagihan,
        ])
            ->with('selected_penarikan_petugas', $validated['petugas'])
            ->with('success', 'Data penarikan berhasil disimpan untuk petugas terpilih.');
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
            ->with('selected_penarikan_petugas', $validated['petugas'])
            ->with('success', 'Data penarikan berhasil diperbarui.');
    }

    private function defaultTahun(): int
    {
        return now()->year;
    }

    private function defaultBulan(int $tahun): ?int
    {
        return Tagihan::query()
            ->where('tahun_tagihan', $tahun)
            ->max('bulan_tagihan');
    }

    private function tagihansByPeriode(?int $bulan, ?int $tahun, array $excludedTagihanIds = []): Collection
    {
        return Tagihan::query()
            ->when($tahun, fn ($query) => $query->where('tahun_tagihan', $tahun))
            ->when($bulan, fn ($query) => $query->where('bulan_tagihan', $bulan))
            ->when($excludedTagihanIds, fn ($query) => $query->whereNotIn('id', $excludedTagihanIds))
            ->orderBy('nama_instansi')
            ->get();
    }
}
