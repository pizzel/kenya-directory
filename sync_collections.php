<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\DiscoveryCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

echo "Syncing DiscoveryCollection cover images...\n";

$collections = DiscoveryCollection::all();
foreach ($collections as $collection) {
    $oldPath = $collection->getRawOriginal('cover_image_url');
    if (!$oldPath) continue;

    // Pattern: 12365/filename.jpg
    if (preg_match('/^(\d+)\/(.+)$/', $oldPath, $matches)) {
        $mediaId = $matches[1];
        $media = Media::find($mediaId);
        
        if ($media) {
            $newPath = $mediaId . '/' . $media->file_name;
            if ($oldPath !== $newPath) {
                echo "Updating Collection [{$collection->id}]: $oldPath -> $newPath\n";
                $collection->update(['cover_image_url' => $newPath]);
            }
        }
    }
}

echo "Sync complete!\n";
