<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Business;

$b = Business::whereHas('media', function($q) { $q->where('collection_name', 'images'); })->first();
$m = $b->getFirstMedia('images');

echo "Business: " . $b->name . "\n";
echo "Media ID: " . $m->id . "\n";
echo "Media File: " . $m->file_name . "\n";
echo "Disk Path: " . $m->getPath() . "\n";

$dir = dirname($m->getPath());
echo "Scanning directory: $dir\n";

if (is_dir($dir)) {
    $files = scandir($dir);
    print_r($files);
    
    $convDir = $dir . '/conversions';
    echo "Scanning conversions directory: $convDir\n";
    if (is_dir($convDir)) {
        print_r(scandir($convDir));
    } else {
        echo "Conversions directory MISSING\n";
    }
} else {
    echo "Directory MISSING\n";
}
