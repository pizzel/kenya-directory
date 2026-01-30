<?php

namespace App\Support\PathGenerators;

use Spatie\MediaLibrary\Support\PathGenerator\PathGenerator;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use App\Models\Business;

class BusinessPathGenerator implements PathGenerator
{
    public function getPath(Media $media): string
    {
        // Only modify path for Business model
        if ($media->model_type === Business::class || $media->model_type === 'App\Models\Business') {
            // Structure: businesses/{business_id}/{media_id}/
            return 'businesses/' . $media->model_id . '/' . $media->id . '/';
        }

        // Default behavior for other models: {media_id}/
        return $media->id . '/';
    }

    public function getPathForConversions(Media $media): string
    {
        return $this->getPath($media) . 'conversions/';
    }

    public function getPathForResponsiveImages(Media $media): string
    {
        return $this->getPath($media) . 'responsive-images/';
    }
}
