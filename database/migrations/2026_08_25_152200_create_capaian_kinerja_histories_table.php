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
        Schema::create('capaian_kinerja_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('capaian_kinerja_id')->constrained('capaian_kinerjas')->onDelete('cascade');
            $table->text('link_bukti_kinerja')->nullable();
            $table->text('link_bukti_tindak_lanjut')->nullable();
            $table->text('penjelasan_lainnya')->nullable();
            $table->text('dasar_hitung')->nullable();
            $table->text('argumen_logis')->nullable();
            $table->text('target_realisasi')->nullable();
            $table->text('catatan_pimpinan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('capaian_kinerja_histories');
    }
};
