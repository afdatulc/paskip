<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Perjanjian Kinerja (PK) Tahunan Kepala BPS.
     * Target awal diambil dari Renstra, bisa direvisi.
     */
    public function up(): void
    {
        Schema::create('pk_tahunans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('indikators')->cascadeOnDelete();
            $table->integer('tahun');
            $table->decimal('target_awal', 15, 2)->comment('Diambil dari target Renstra');
            $table->decimal('target_revisi', 15, 2)->nullable()->comment('Diisi jika ada revisi target');
            $table->boolean('status_revisi')->default(false);
            $table->text('alasan_revisi')->nullable();
            $table->timestamps();

            $table->unique(['indikator_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pk_tahunans');
    }
};
