<?php

namespace App\Http\Controllers;

use App\Models\OtherTransaction;
use App\Models\TagihanPenarikan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class FinancialRecapController extends Controller
{
    public function index(): View
    {
        $penarikans = TagihanPenarikan::query()
            ->with('tagihan')
            ->orderByDesc('created_at')
            ->get();

        $otherTransactions = OtherTransaction::query()
            ->orderByDesc('tanggal')
            ->get();

        $totalPendapatan = $penarikans->sum('nominal') + $otherTransactions
            ->where('jenis', 'pendapatan')
            ->sum('nominal');

        $totalPengeluaran = $otherTransactions
            ->where('jenis', 'pengeluaran')
            ->sum('nominal');

        $monthlyRecap = $this->buildMonthlyRecap($penarikans, $otherTransactions);
        $history = $this->buildHistory($penarikans, $otherTransactions);

        return view('rekap.keuangan', [
            'totalPendapatan' => $totalPendapatan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldo' => $totalPendapatan - $totalPengeluaran,
            'monthlyRecap' => $monthlyRecap,
            'history' => $history,
        ]);
    }

    private function buildMonthlyRecap(Collection $penarikans, Collection $otherTransactions): Collection
    {
        $monthly = [];

        $addToMonth = function (Carbon $tanggal, string $jenis, int $nominal) use (&$monthly): void {
            $key = $tanggal->format('Y-m');

            if (! isset($monthly[$key])) {
                $monthly[$key] = [
                    'label' => $tanggal->translatedFormat('F Y'),
                    'pendapatan' => 0,
                    'pengeluaran' => 0,
                ];
            }

            $monthly[$key][$jenis] += $nominal;
        };

        foreach ($penarikans as $penarikan) {
            $tanggal = $penarikan->created_at ? Carbon::parse($penarikan->created_at) : now();
            $addToMonth($tanggal, 'pendapatan', (int) $penarikan->nominal);
        }

        foreach ($otherTransactions as $transaction) {
            $tanggal = $transaction->tanggal ? Carbon::parse($transaction->tanggal) : now();
            $addToMonth($tanggal, $transaction->jenis, (int) $transaction->nominal);
        }

        return collect($monthly)
            ->map(function (array $row): array {
                $row['saldo'] = $row['pendapatan'] - $row['pengeluaran'];

                return $row;
            })
            ->sortKeysDesc();
    }

    private function buildHistory(Collection $penarikans, Collection $otherTransactions): Collection
    {
        $history = collect();

        foreach ($penarikans as $penarikan) {
            $history->push([
                'tanggal' => $penarikan->created_at ? Carbon::parse($penarikan->created_at) : now(),
                'jenis' => 'pendapatan',
                'sumber' => 'Entri Penarikan',
                'kategori' => $penarikan->petugas ? 'Petugas: '.$penarikan->petugas : 'Penarikan',
                'deskripsi' => $penarikan->nama_pelanggan,
                'nominal' => (int) $penarikan->nominal,
            ]);
        }

        foreach ($otherTransactions as $transaction) {
            $history->push([
                'tanggal' => $transaction->tanggal ? Carbon::parse($transaction->tanggal) : now(),
                'jenis' => $transaction->jenis,
                'sumber' => 'Pendapatan/Pengeluaran Lainnya',
                'kategori' => $transaction->kategori,
                'deskripsi' => $transaction->pihak ?? $transaction->deskripsi,
                'nominal' => (int) $transaction->nominal,
            ]);
        }

        return $history
            ->sortByDesc('tanggal')
            ->values();
    }
}
