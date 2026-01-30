<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DiscoveryCollection;

$c = DiscoveryCollection::whereNotNull('cover_image_url')->first();
if (!$c) {
    echo "No collection with cover image found.\n";
    exit;
}

$path = storage_path('app/public/' . $c->cover_image_url);
echo "Collection: " . $c->title . "\n";
echo "Cover Image Path: " . $path . "\n";
echo "File Exists: " . (file_exists($path) ? 'Yes' : 'No') . "\n";

$dir = dirname($path);
if (is_dir($dir)) {
    echo "Directory contents of $dir:\n";
    print_r(scandir($dir));
} else {
    echo "Directory $dir DOES NOT EXIST!\n";
}
