<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('capaian_kinerjas', function (Blueprint $table) {
            $table->enum('status_approval', ['Menunggu', 'Disetujui', 'Ditolak'])->default('Menunggu')->after('target_realisasi');
            $table->text('catatan_pimpinan')->nullable()->after('status_approval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capaian_kinerjas', function (Blueprint $table) {
            $table->dropColumn(['status_approval', 'catatan_pimpinan']);
        });
    }
};
