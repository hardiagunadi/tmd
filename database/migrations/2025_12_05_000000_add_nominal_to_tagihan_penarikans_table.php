<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tagihan_penarikans', function (Blueprint $table) {
            $table->unsignedBigInteger('nominal')->default(0)->after('petugas');
        });

        $tagihanTotals = DB::table('tagihans')
            ->select('id', DB::raw('biaya_langganan + biaya_admin as total'))
            ->pluck('total', 'id');

        DB::table('tagihan_penarikans')
            ->orderBy('id')
            ->chunkById(100, function ($penarikans) use ($tagihanTotals): void {
                foreach ($penarikans as $penarikan) {
                    DB::table('tagihan_penarikans')
                        ->where('id', $penarikan->id)
                        ->update([
                            'nominal' => $tagihanTotals[$penarikan->tagihan_id] ?? 0,
                        ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tagihan_penarikans', function (Blueprint $table) {
            $table->dropColumn('nominal');
        });
    }
};
