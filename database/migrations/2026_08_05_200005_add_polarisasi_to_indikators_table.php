<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom polarisasi ke indikators.
     * Default 'positif' (makin tinggi makin baik).
     */
    public function up(): void
    {
        Schema::table('indikators', function (Blueprint $table) {
            $table->enum('polarisasi', ['positif', 'negatif'])
                  ->default('positif')
                  ->after('tipe')
                  ->comment('Positif = makin tinggi makin baik, Negatif = makin rendah makin baik');
        });
    }

    public function down(): void
    {
        Schema::table('indikators', function (Blueprint $table) {
            $table->dropColumn('polarisasi');
        });
    }
};
