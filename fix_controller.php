<?php
$file = 'c:/Users/Afdatul Chofidah/OneDrive/Documents/2. LATSAR/paskip/app/Http/Controllers/TemplateWordController.php';
$lines = file($file);

// Remove lines from index 42 up to index 235
array_splice($lines, 42, 194);

file_put_contents($file, implode('', $lines));
echo "Fixed";
