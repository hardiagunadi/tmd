<?php

namespace App\Http\Controllers;

use App\Models\PendapatanLain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class PendapatanLainController extends Controller
{
    private const PETUGAS = ['Deswi', 'Slamet', 'Ade'];

    public function index(): View
    {
        $entries = PendapatanLain::query()
            ->latest()
            ->get()
            ->groupBy('petugas');

        $totals = $this->petugasTotals($entries);

        return view('pendapatan.lain', [
            'entries' => $entries,
            'totals' => $totals,
            'petugasList' => self::PETUGAS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'petugas' => 'required|in:'.implode(',', self::PETUGAS),
            'keterangan' => 'required|string|max:255',
            'pendapatan' => 'required|integer|min:0',
            'pengeluaran' => 'nullable|integer|min:0',
        ]);

        PendapatanLain::create([
            'petugas' => $validated['petugas'],
            'keterangan' => $validated['keterangan'],
            'pendapatan' => (int) $request->integer('pendapatan'),
            'pengeluaran' => (int) $request->integer('pengeluaran', 0),
        ]);

        return redirect()
            ->route('pendapatan-lain.index')
            ->with('success', 'Pendapatan lain-lain berhasil dicatat.')
            ->with('last_pendapatan_petugas', $validated['petugas']);
    }

    public function destroy(PendapatanLain $pendapatanLain): RedirectResponse
    {
        $pendapatanLain->delete();

        return Redirect::route('pendapatan-lain.index')
            ->with('success', 'Entri pendapatan lain-lain dihapus.');
    }

    private function petugasTotals(Collection $entries): Collection
    {
        return collect(self::PETUGAS)
            ->mapWithKeys(function (string $petugas) use ($entries): array {
                $items = $entries->get($petugas) ?? collect();

                $totalPendapatan = $items->sum(fn (PendapatanLain $entry): int => $entry->pendapatan ?? 0);
                $totalPengeluaran = $items->sum(fn (PendapatanLain $entry): int => $entry->pengeluaran ?? 0);

                return [
                    $petugas => [
                        'pendapatan' => $totalPendapatan,
                        'pengeluaran' => $totalPengeluaran,
                        'bersih' => $totalPendapatan - $totalPengeluaran,
                    ],
                ];
            });
    }
}
