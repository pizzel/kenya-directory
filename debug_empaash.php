<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Business;

$b = Business::where('name', 'like', 'Empaash%')->first();
if (!$b) {
    echo "Business not found\n";
    exit;
}

echo "Business: " . $b->name . "\n";
echo "getImageUrl('thumbnail'): " . $b->getImageUrl('thumbnail') . "\n";

$m = $b->getFirstMedia('images');
if ($m) {
    echo "Media ID: " . $m->id . "\n";
    echo "File Name: " . $m->file_name . "\n";
    echo "Path (thumbnail): " . $m->getPath('thumbnail') . "\n";
    echo "Exists (thumbnail): " . (file_exists($m->getPath('thumbnail')) ? 'Yes' : 'No') . "\n";
} else {
    echo "No media found for 'images' collection.\n";
    $allMedia = $b->media;
    echo "Total media count: " . $allMedia->count() . "\n";
    foreach($allMedia as $item) {
        echo " - Collection: " . $item->collection_name . ", ID: " . $item->id . "\n";
    }
}
