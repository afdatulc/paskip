<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;

$tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' AND name NOT IN ('migrations', 'failed_jobs', 'password_reset_tokens', 'personal_access_tokens', 'sessions', 'cache', 'cache_locks', 'jobs', 'job_batches')");

echo "erDiagram\n";

foreach ($tables as $table) {
    $tableName = $table->name;
    echo "    $tableName {\n";
    $columns = DB::select("PRAGMA table_info($tableName)");
    foreach ($columns as $column) {
        $type = preg_replace('/[^a-zA-Z0-9_]/', '', $column->type);
        if (empty($type)) $type = "string";
        $pk = $column->pk ? " PK" : "";
        echo "        $type $column->name$pk\n";
    }
    echo "    }\n\n";
    
    // foreign keys
    $fks = DB::select("PRAGMA foreign_key_list($tableName)");
    foreach ($fks as $fk) {
        // format: Table }|--|| Foreign : "relation"
        echo "    $tableName }|--|| $fk->table : \"$fk->from -> $fk->to\"\n";
    }
}
