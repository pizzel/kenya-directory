<?php

namespace App\Filament\Resources\DiscoveryCollectionResource\Pages;

use App\Filament\Resources\DiscoveryCollectionResource;
use App\Models\Business;
use Filament\Resources\Pages\CreateRecord;

class CreateDiscoveryCollection extends CreateRecord
{
    protected static string $resource = DiscoveryCollectionResource::class;

    // This method runs AFTER the record has been successfully created.
    protected function afterCreate(): void
    {
        $collection = $this->record;

        if (!empty($collection->cover_image_url)) {
            return;
        }

        $firstBusinessWithImage = $collection->businesses()
            ->whereHas('media', fn($q) => $q->where('collection_name', 'images'))
            ->with('media')
            ->first();

        if ($firstBusinessWithImage) {
            $mediaId = $firstBusinessWithImage->getFirstMedia('images')?->id;
            
            if ($mediaId) {
                $collection->update(['cover_image_url' => $mediaId]);
            }
        }
    }
}