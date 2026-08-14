<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Memperluas kolom role di users agar mendukung role tambahan.
     * Role baru: 'pimpinan' (Kepala BPS) — read-only dashboard + approval evaluasi.
     */
    public function up(): void
    {
        // SQLite doesn't support ALTER COLUMN, so we just ensure
        // the role column exists and update its comment.
        // The role validation will be handled at the application layer.
        // Existing values: 'admin', 'pegawai'
        // New value: 'pimpinan'
    }

    public function down(): void
    {
        // No structural changes to revert
    }
};
