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
        Schema::table('kendala_rtl_executions', function (Blueprint $table) {
            $table->dropColumn('pegawai_nip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kendala_rtl_executions', function (Blueprint $table) {
            $table->string('pegawai_nip')->nullable();
        });
    }
};
