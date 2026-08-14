<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel master periode Rencana Strategis (misal: 2025-2029).
     * Mendukung penyimpanan historis untuk periode lama.
     */
    public function up(): void
    {
        Schema::create('renstras', function (Blueprint $table) {
            $table->id();
            $table->integer('tahun_awal')->comment('Tahun mulai Renstra, misal: 2025');
            $table->integer('tahun_akhir')->comment('Tahun selesai Renstra, misal: 2029');
            $table->enum('status', ['aktif', 'nonaktif'])->default('nonaktif');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->unique(['tahun_awal', 'tahun_akhir']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('renstras');
    }
};
