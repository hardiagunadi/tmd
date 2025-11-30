<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\TagihanPenarikan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\View\View;

class PenarikanController extends Controller
{
    private const PETUGAS = ['Deswi', 'Slamet', 'Ade'];

    public function index(): View
    {
        $now = now();

        $assignedTagihanIds = TagihanPenarikan::query()
            ->whereHas('tagihan', function ($query) use ($now) {
                $query
                    ->where('bulan_tagihan', $now->month)
                    ->where('tahun_tagihan', $now->year);
            })
            ->pluck('tagihan_id');

        $printedTagihans = Tagihan::query()
            ->where('bulan_tagihan', $now->month)
            ->where('tahun_tagihan', $now->year)
            ->whereNotNull('printed_at')
            ->whereNotIn('id', $assignedTagihanIds)
            ->orderBy('nama_instansi')
            ->get([
                'id',
                'nama_instansi',
                'no_invoice',
                'biaya_langganan',
                'biaya_admin',
            ]);

        $penarikans = TagihanPenarikan::with('tagihan')
            ->whereHas('tagihan', function ($query) use ($now) {
                $query
                    ->where('bulan_tagihan', $now->month)
                    ->where('tahun_tagihan', $now->year);
            })
            ->latest()
            ->get()
            ->groupBy('petugas');

        $totals = $this->petugasTotals($penarikans);

        $petugasPreference = session('penarikan_petugas');

        return view('tagihan.penarikan', [
            'printedTagihans' => $printedTagihans,
            'penarikans' => $penarikans,
            'petugasList' => self::PETUGAS,
            'totals' => $totals,
            'petugasPreference' => $petugasPreference,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'petugas' => 'required|in:'.implode(',', self::PETUGAS),
            'tagihan_id' => 'required|exists:tagihans,id',
        ]);

        $now = now();

        $tagihan = Tagihan::query()
            ->where('bulan_tagihan', $now->month)
            ->where('tahun_tagihan', $now->year)
            ->whereNotNull('printed_at')
            ->find($validated['tagihan_id']);

        if (! $tagihan) {
            return Redirect::back()
                ->withInput()
                ->with('error', 'Tagihan harus berasal dari bulan tagihan saat ini dan sudah dicetak.');
        }

        $existingPenarikan = TagihanPenarikan::query()
            ->where('tagihan_id', $tagihan->id)
            ->whereHas('tagihan', function ($query) use ($now) {
                $query
                    ->where('bulan_tagihan', $now->month)
                    ->where('tahun_tagihan', $now->year);
            })
            ->exists();

        if ($existingPenarikan) {
            return Redirect::back()
                ->withInput()
                ->with('error', 'Tagihan sudah direkap untuk bulan ini.');
        }

        TagihanPenarikan::create([
            'tagihan_id' => $tagihan->id,
            'nama_pelanggan' => $tagihan->nama_instansi,
            'petugas' => $validated['petugas'],
            'nominal' => $tagihan->total_bayar,
        ]);

        Session::put('penarikan_petugas', $validated['petugas']);

        return redirect()
            ->route('penarikan.index')
            ->with('success', 'Rekap penarikan berhasil disimpan.');
    }

    public function destroy(TagihanPenarikan $penarikan): RedirectResponse
    {
        $now = now();

        if (! $penarikan->tagihan || $penarikan->tagihan->bulan_tagihan !== $now->month || $penarikan->tagihan->tahun_tagihan !== $now->year) {
            return Redirect::back()->with('error', 'Data penarikan tidak ditemukan untuk bulan ini.');
        }

        $penarikan->delete();

        return redirect()
            ->route('penarikan.index')
            ->with('success', 'Data penarikan telah dihapus.');
    }

    public function update(Request $request, TagihanPenarikan $penarikan): RedirectResponse
    {
        $now = now();

        if (! $penarikan->tagihan || $penarikan->tagihan->bulan_tagihan !== $now->month || $penarikan->tagihan->tahun_tagihan !== $now->year) {
            return Redirect::back()->with('error', 'Data penarikan tidak ditemukan untuk bulan ini.');
        }

        $request->validate([
            'nominal' => 'required|integer|min:0|max:'.$penarikan->tagihan->total_bayar,
        ]);

        $penarikan->update([
            'nominal' => (int) $request->integer('nominal'),
        ]);

        return redirect()
            ->route('penarikan.index')
            ->with('success', 'Nominal penarikan telah diperbarui.');
    }

    private function petugasTotals(Collection $penarikans): Collection
    {
        return collect(self::PETUGAS)
            ->mapWithKeys(function (string $petugas) use ($penarikans): array {
                $items = $penarikans->get($petugas) ?? collect();

                return [
                    $petugas => [
                        'count' => $items->count(),
                        'amount' => $items->sum(fn (TagihanPenarikan $penarikan): int => $penarikan->nominal ?? 0),
                    ],
                ];
            });
    }
}
