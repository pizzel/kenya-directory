<?php

use App\Models\Business;
use Illuminate\Support\Facades\Storage;

$business = Business::has('media')->first();
if ($business) {
    $media = $business->getFirstMedia('images');
    if ($media) {
        echo "Media Class: " . get_class($media) . PHP_EOL;
        echo "Has getPathRelativeToRoot: " . (method_exists($media, 'getPathRelativeToRoot') ? 'YES' : 'NO') . PHP_EOL;
        
        // Try to verify what it returns if it exists
        if (method_exists($media, 'getPathRelativeToRoot')) {
            echo "Result: " . $media->getPathRelativeToRoot() . PHP_EOL;
        }
    } else {
        echo "No media found on first business.";
    }
} else {
    echo "No business with media found.";
}
