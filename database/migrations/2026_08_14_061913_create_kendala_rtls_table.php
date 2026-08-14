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
        Schema::create('kendala_rtls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('indikators')->onDelete('cascade');
            $table->integer('triwulan');
            $table->integer('tahun');
            $table->text('kendala');
            $table->text('solusi');
            $table->text('rtl');
            $table->date('batas_waktu')->nullable();
            $table->string('pic_nip')->nullable();
            $table->enum('status', ['Belum Ditindak Lanjut', 'Sudah Ditindak Lanjut'])->default('Belum Ditindak Lanjut');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendala_rtls');
    }
};
