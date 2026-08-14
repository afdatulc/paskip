<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

DB::table('migrations')->whereIn('migration', [
    '2026_07_14_160106_create_issues_table',
    '2026_07_14_160107_create_rtl_executions_table',
    '2026_07_14_160107_create_rtls_table'
])->delete();

echo "Deleted migration records.\n";
