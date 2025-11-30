<?php

namespace App\Http\Controllers;

use App\Models\PendapatanLain;
use App\Models\PetugasGaji;
use App\Models\TagihanPenarikan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class RekapKeuanganController extends Controller
{
    private const PETUGAS = ['Deswi', 'Slamet', 'Ade'];

    public function index(): View
    {
        $now = now();

        $penarikanTotals = TagihanPenarikan::query()
            ->selectRaw('petugas, SUM(nominal) as total')
            ->whereHas('tagihan', function ($query) use ($now) {
                $query
                    ->where('bulan_tagihan', $now->month)
                    ->where('tahun_tagihan', $now->year);
            })
            ->groupBy('petugas')
            ->pluck('total', 'petugas');

        $pendapatanLain = PendapatanLain::query()
            ->whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->latest()
            ->get()
            ->groupBy('petugas');

        $pendapatanLainTotals = $this->pendapatanLainTotals($pendapatanLain);

        $gajiRecords = PetugasGaji::query()
            ->where('bulan', $now->month)
            ->where('tahun', $now->year)
            ->get()
            ->keyBy('petugas');

        $totalPenarikan = $penarikanTotals->sum();
        $totalGaji = $gajiRecords->sum('nominal');

        $pendapatanKotor = $totalPenarikan + $pendapatanLainTotals['pendapatan'];
        $totalPengeluaran = $pendapatanLainTotals['pengeluaran'] + $totalGaji;
        $bersih = $pendapatanKotor - $totalPengeluaran;

        return view('rekap.keuangan', [
            'petugasList' => self::PETUGAS,
            'penarikanTotals' => $penarikanTotals,
            'pendapatanLainPerPetugas' => $pendapatanLainTotals['per_petugas'],
            'pendapatanLainSummary' => $pendapatanLainTotals,
            'gajiRecords' => $gajiRecords,
            'summary' => [
                'pendapatan_penarikan' => $totalPenarikan,
                'pendapatan_lain' => $pendapatanLainTotals['pendapatan'],
                'pengeluaran_lain' => $pendapatanLainTotals['pengeluaran'],
                'gaji' => $totalGaji,
                'pendapatan_kotor' => $pendapatanKotor,
                'pengeluaran' => $totalPengeluaran,
                'bersih' => $bersih,
            ],
            'periodeLabel' => $now->translatedFormat('F Y'),
        ]);
    }

    public function storeGaji(Request $request): RedirectResponse
    {
        $now = now();

        $validated = $request->validate([
            'gaji' => 'required|array',
            'gaji.*' => 'nullable|integer|min:0',
        ]);

        foreach (self::PETUGAS as $petugas) {
            PetugasGaji::updateOrCreate(
                [
                    'petugas' => $petugas,
                    'bulan' => $now->month,
                    'tahun' => $now->year,
                ],
                [
                    'nominal' => (int) ($validated['gaji'][$petugas] ?? 0),
                ]
            );
        }

        return Redirect::route('rekap-keuangan.index')
            ->with('success', 'Gaji petugas telah diperbarui.');
    }

    /**
     * @param  Collection<string, Collection<int, PendapatanLain>>  $entries
     */
    private function pendapatanLainTotals(Collection $entries): array
    {
        $perPetugas = collect(self::PETUGAS)
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

        $totalPendapatan = $perPetugas->sum(fn (array $item): int => $item['pendapatan']);
        $totalPengeluaran = $perPetugas->sum(fn (array $item): int => $item['pengeluaran']);

        return [
            'pendapatan' => $totalPendapatan,
            'pengeluaran' => $totalPengeluaran,
            'bersih' => $totalPendapatan - $totalPengeluaran,
            'per_petugas' => $perPetugas,
        ];
    }
}
