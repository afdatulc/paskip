<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Target Renstra per-tahun per-IKU.
     * Link ke tabel indikators yang sudah ada (backward-compatible).
     */
    public function up(): void
    {
        Schema::create('target_renstras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('renstra_id')->constrained('renstras')->cascadeOnDelete();
            $table->foreignId('indikator_id')->constrained('indikators')->cascadeOnDelete();
            $table->integer('tahun')->comment('Tahun spesifik, misal: 2025');
            $table->decimal('target', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['renstra_id', 'indikator_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('target_renstras');
    }
};
