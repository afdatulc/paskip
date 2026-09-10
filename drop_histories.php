<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

try {
    Schema::dropIfExists('capaian_kinerja_histories');
    echo "Dropped capaian_kinerja_histories\n";
    // Also delete the migration from the migrations table if it exists
    \Illuminate\Support\Facades\DB::table('migrations')->where('migration', 'like', '%create_capaian_kinerja_histories_table%')->delete();
    \Illuminate\Support\Facades\DB::table('migrations')->where('migration', 'like', '%add_realisasi_to_histories_table%')->delete();
    echo "Cleared migrations\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
