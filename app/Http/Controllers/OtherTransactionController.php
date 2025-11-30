<?php

namespace App\Http\Controllers;

use App\Models\OtherTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OtherTransactionController extends Controller
{
    public function index(): View
    {
        $transactions = OtherTransaction::query()
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $totalPendapatan = $transactions
            ->where('jenis', 'pendapatan')
            ->sum('nominal');

        $totalPengeluaran = $transactions
            ->where('jenis', 'pengeluaran')
            ->sum('nominal');

        return view('lainnya.index', [
            'transactions' => $transactions,
            'totalPendapatan' => $totalPendapatan,
            'totalPengeluaran' => $totalPengeluaran,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jenis' => 'required|in:pendapatan,pengeluaran',
            'kategori' => 'required|string|max:100',
            'pihak' => 'nullable|string|max:150',
            'deskripsi' => 'nullable|string|max:300',
            'nominal' => 'required|integer|min:0',
        ]);

        OtherTransaction::create($validated);

        $pesan = $validated['jenis'] === 'pendapatan'
            ? 'Pendapatan berhasil dicatat.'
            : 'Pengeluaran berhasil dicatat.';

        return redirect()
            ->route('lainnya.index')
            ->with('success', $pesan);
    }
}
