<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('petugas_gajis', function (Blueprint $table) {
            $table->id();
            $table->string('petugas');
            $table->unsignedTinyInteger('bulan');
            $table->unsignedSmallInteger('tahun');
            $table->unsignedBigInteger('nominal')->default(0);
            $table->timestamps();

            $table->unique(['petugas', 'bulan', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('petugas_gajis');
    }
};
