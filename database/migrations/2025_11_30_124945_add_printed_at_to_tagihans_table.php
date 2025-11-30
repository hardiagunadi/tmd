<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_printed_at_to_tagihans_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->timestamp('printed_at')->nullable()->after('deskripsi_paket');
        });
    }

    public function down(): void
    {
        Schema::table('tagihans', function (Blueprint $table) {
            $table->dropColumn('printed_at');
        });
    }
};
