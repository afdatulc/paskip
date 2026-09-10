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
        Schema::dropIfExists('rtl_executions');
        Schema::dropIfExists('rtls');
        Schema::dropIfExists('issues');
        Schema::dropIfExists('analisis');
        Schema::dropIfExists('output_realisasis');
        Schema::dropIfExists('output_masters');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down migration since this is a cleanup of obsolete tables
    }
};
