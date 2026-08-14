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
        Schema::create('kendala_rtl_executions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kendala_rtl_id')->constrained('kendala_rtls')->onDelete('cascade');
            $table->text('narasi_tindak_lanjut');
            $table->date('tanggal_pelaksanaan');
            $table->boolean('is_dokumentasi_ada')->default(false);
            $table->boolean('is_timestamp_ada')->default(false);
            $table->string('pegawai_nip'); // User who submitted
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kendala_rtl_executions');
    }
};
