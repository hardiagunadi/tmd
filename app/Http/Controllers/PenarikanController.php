<?php

namespace App\Http\Controllers;

use App\Models\Tagihan;
use App\Models\TagihanPenarikan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class PenarikanController extends Controller
{
    private const PETUGAS = ['Deswi', 'Slamet', 'Ade'];

    public function index(): View
    {
        $printedTagihans = Tagihan::query()
            ->whereNotNull('printed_at')
            ->orderBy('nama_instansi')
            ->get(['id', 'nama_instansi', 'no_invoice']);

        $penarikans = TagihanPenarikan::with('tagihan')
            ->latest()
            ->get()
            ->groupBy('petugas');

        $totals = $this->petugasTotals($penarikans);

        return view('tagihan.penarikan', [
            'printedTagihans' => $printedTagihans,
            'penarikans' => $penarikans,
            'petugasList' => self::PETUGAS,
            'totals' => $totals,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'petugas' => 'required|in:'.implode(',', self::PETUGAS),
            'tagihan_id' => 'nullable|exists:tagihans,id',
            'nama_pelanggan' => 'nullable|string|max:255',
        ]);

        $tagihan = null;

        if (! empty($validated['tagihan_id'])) {
            $tagihan = Tagihan::query()
                ->whereNotNull('printed_at')
                ->find($validated['tagihan_id']);

            if (! $tagihan) {
                return back()
                    ->withInput()
                    ->with('error', 'Tagihan harus sudah dicetak sebelum direkap.');
            }
        }

        $namaPelanggan = $validated['nama_pelanggan'] ?? '';

        if (! $namaPelanggan && $tagihan) {
            $namaPelanggan = $tagihan->nama_instansi;
        }

        if (! $namaPelanggan) {
            return back()
                ->withInput()
                ->with('error', 'Masukkan nama pelanggan atau pilih tagihan tercetak.');
        }

        TagihanPenarikan::create([
            'tagihan_id' => $tagihan?->id,
            'nama_pelanggan' => $namaPelanggan,
            'petugas' => $validated['petugas'],
        ]);

        return redirect()
            ->route('penarikan.index')
            ->with('success', 'Rekap penarikan berhasil disimpan.');
    }

    private function petugasTotals(Collection $penarikans): Collection
    {
        return collect(self::PETUGAS)
            ->mapWithKeys(function (string $petugas) use ($penarikans): array {
                return [
                    $petugas => $penarikans->get($petugas)?->count() ?? 0,
                ];
            });
    }
}
