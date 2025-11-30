<?php

// database/migrations/2025_01_01_000000_create_tagihans_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_instansi');             // SMP N3 Watumalang
            $table->text('alamat_instansi')->nullable(); // Dusun Binangun, ...
            $table->string('no_invoice')->unique();
            $table->string('no_pelanggan');
            $table->unsignedTinyInteger('bulan_tagihan');   // 1–12
            $table->unsignedSmallInteger('tahun_tagihan');  // 2025
            $table->unsignedBigInteger('biaya_langganan');  // 550000
            $table->unsignedBigInteger('biaya_admin')->default(0);
            $table->string('deskripsi_paket')->nullable();  // High Speed Internet...
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihans');
    }
};
