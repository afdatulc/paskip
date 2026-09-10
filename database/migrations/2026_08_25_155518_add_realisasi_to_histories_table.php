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
        Schema::table('capaian_kinerja_histories', function (Blueprint $table) {
            $table->decimal('realisasi_kumulatif', 10, 2)->nullable()->after('capaian_kinerja_id');
            $table->decimal('realisasi_x', 15, 2)->nullable()->after('realisasi_kumulatif');
            $table->decimal('realisasi_y', 15, 2)->nullable()->after('realisasi_x');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('capaian_kinerja_histories', function (Blueprint $table) {
            $table->dropColumn(['realisasi_kumulatif', 'realisasi_x', 'realisasi_y']);
        });
    }
};
