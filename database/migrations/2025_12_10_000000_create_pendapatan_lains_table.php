<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pendapatan_lains', function (Blueprint $table): void {
            $table->id();
            $table->string('petugas');
            $table->string('keterangan');
            $table->unsignedBigInteger('pendapatan');
            $table->unsignedBigInteger('pengeluaran')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pendapatan_lains');
    }
};
