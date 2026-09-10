<?php
$dir = __DIR__.'/app/Http/Controllers';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isDir()) continue;
    if (pathinfo($file, PATHINFO_EXTENSION) !== 'php') continue;
    
    $content = file_get_contents($file);
    
    // Replace tahun
    $content = preg_replace("/\\\$request->get\('tahun', date\('Y'\)\)/", "\$request->get('tahun', session('global_tahun', date('Y')))", $content);
    $content = preg_replace("/\\\$request->get\('tahun', \\\$defaultTahun\)/", "\$request->get('tahun', session('global_tahun', \$defaultTahun))", $content);
    
    // Replace triwulan
    $content = preg_replace("/\\\$request->get\('triwulan', min\(ceil\(date\('n'\) \/ 3\), 4\)\)/", "\$request->get('triwulan', session('global_triwulan', min(ceil(date('n') / 3), 4)))", $content);
    $content = preg_replace("/\\\$request->get\('triwulan', ceil\(date\('n'\) \/ 3\)\)/", "\$request->get('triwulan', session('global_triwulan', ceil(date('n') / 3)))", $content);
    $content = preg_replace("/\\\$request->get\('triwulan', \\\$defaultTriwulan\)/", "\$request->get('triwulan', session('global_triwulan', \$defaultTriwulan))", $content);
    
    file_put_contents($file, $content);
}
echo "Done replacing session filters.\n";
