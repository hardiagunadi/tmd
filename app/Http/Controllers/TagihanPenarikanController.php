<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\TagihanPenarikan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagihanPenarikanController extends Controller
{
    /**
     * @var array<int, string>
     */
    private array $petugasList = ['Deswi', 'Slamet', 'Ade', 'Hardi'];

    public function index(): View
    {
        $now = now();

        $printedTagihans = Tagihan::query()
            ->whereNotNull('printed_at')
            ->where('bulan_tagihan', $now->month)
            ->where('tahun_tagihan', $now->year)
            ->whereDoesntHave('penarikans')
            ->orderBy('nama_instansi')
            ->get();

        $currentPenarikans = TagihanPenarikan::with('tagihan')
            ->where(function ($query) use ($now) {
                $query->whereHas('tagihan', function ($tagihanQuery) use ($now) {
                    $tagihanQuery->where('bulan_tagihan', $now->month)
                        ->where('tahun_tagihan', $now->year);
                })->orWhere(function ($manualQuery) use ($now) {
                    $manualQuery->whereNull('tagihan_id')
                        ->whereYear('created_at', $now->year)
                        ->whereMonth('created_at', $now->month);
                });
            })
            ->orderByDesc('created_at')
            ->get();

        $penarikansByPetugas = collect();
        foreach ($this->petugasList as $petugas) {
            $penarikansByPetugas->put($petugas, $currentPenarikans->where('petugas', $petugas));
        }

        foreach ($currentPenarikans->groupBy('petugas') as $petugas => $entries) {
            if (! $penarikansByPetugas->has($petugas)) {
                $penarikansByPetugas->put($petugas, $entries);
            }
        }

        $petugasSummary = $penarikansByPetugas->map(function ($entries) {
            return [
                'jumlah' => $entries->count(),
                'total_nominal' => $entries->sum('nominal'),
            ];
        });

        return view('tagihan.penarikan', [
            'petugasList' => $this->petugasList,
            'printedTagihans' => $printedTagihans,
            'penarikansByPetugas' => $penarikansByPetugas,
            'petugasSummary' => $petugasSummary,
            'currentMonthName' => $now->translatedFormat('F Y'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tagihan_id' => 'required|exists:tagihans,id',
            'petugas' => 'required|string|in:Deswi,Slamet,Ade,Hardi',
        ]);

        $tagihanId = $validated['tagihan_id'];
        $now = now();

        $tagihan = Tagihan::whereKey($tagihanId)->firstOrFail();

        if (is_null($tagihan->printed_at)) {
            return back()->withInput()->with('error', 'Tagihan belum dicetak.');
        }

        if ($tagihan->bulan_tagihan !== $now->month || $tagihan->tahun_tagihan !== $now->year) {
            return back()->withInput()->with('error', 'Tagihan bukan untuk bulan berjalan.');
        }

        if (TagihanPenarikan::where('tagihan_id', $tagihanId)->exists()) {
            return back()->withInput()->with('error', 'Tagihan ini sudah direkap.');
        }

        TagihanPenarikan::create([
            'tagihan_id' => $tagihan->id,
            'nama_pelanggan' => $tagihan->nama_instansi,
            'petugas' => $validated['petugas'],
            'nominal' => $tagihan->total_bayar,
        ]);

        return redirect()
            ->route('penarikan.index')
            ->with('success', 'Data penarikan tagihan berhasil disimpan.');
    }

    public function update(TagihanPenarikan $penarikan, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nominal' => 'required|integer|min:0',
        ]);

        $penarikan->update([
            'nominal' => $validated['nominal'],
        ]);

        return redirect()
            ->route('penarikan.index')
            ->with('success', 'Nominal penarikan diperbarui.');
    }

    public function destroy(TagihanPenarikan $penarikan): RedirectResponse
    {
        $penarikan->delete();

        return redirect()
            ->route('penarikan.index')
            ->with('success', 'Data penarikan tagihan dihapus.');
    }
}
