<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Evaluasi akhir tahun sebagai dasar keputusan revisi Renstra.
     */
    public function up(): void
    {
        Schema::create('evaluasi_tahunans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pk_tahunan_id')->constrained('pk_tahunans')->cascadeOnDelete();
            $table->decimal('realisasi_akhir', 15, 2)->default(0);
            $table->decimal('capaian_persentase', 8, 2)->default(0);
            $table->text('analisis_hasil')->nullable()->comment('Narasi analisis mengapa target tercapai/tidak');
            $table->boolean('rekomendasi_revisi')->default(false)->comment('Apakah merekomendasikan revisi target tahun depan');
            $table->text('catatan_revisi')->nullable()->comment('Detail apa yang perlu direvisi');
            $table->timestamps();

            $table->unique('pk_tahunan_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('evaluasi_tahunans');
    }
};
