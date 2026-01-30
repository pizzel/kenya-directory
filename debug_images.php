<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DiscoveryCollection;

$c = DiscoveryCollection::with('businesses')->first();
if (!$c) {
    echo "No collection found\n";
    exit;
}

echo "Collection: " . $c->title . "\n";
echo "Raw DB cover_image_url: " . ($c->getRawOriginal('cover_image_url') ?: 'NULL') . "\n";
echo "Final getCoverImageUrl(): " . $c->getCoverImageUrl() . "\n";

if ($c->businesses->count() > 0) {
    $b = $c->businesses->first();
    echo "First Business: " . $b->name . "\n";
    echo "First Business ID: " . $b->id . "\n";
    $m = $b->getFirstMedia('images');
    if ($m) {
        echo "Media ID: " . $m->id . "\n";
        echo "Media File: " . $m->file_name . "\n";
        echo "Original Path: " . $m->getPath() . "\n";
        echo "Original Exists: " . (file_exists($m->getPath()) ? 'Yes' : 'No') . "\n";
        echo "Card Path: " . $m->getPath('card') . "\n";
        echo "Card Exists: " . (file_exists($m->getPath('card')) ? 'Yes' : 'No') . "\n";
        echo "Thumbnail Path: " . $m->getPath('thumbnail') . "\n";
        echo "Thumbnail Exists: " . (file_exists($m->getPath('thumbnail')) ? 'Yes' : 'No') . "\n";
    } else {
        echo "No media for first business\n";
    }
}
