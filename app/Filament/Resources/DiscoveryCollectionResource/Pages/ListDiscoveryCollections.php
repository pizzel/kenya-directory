<?php

namespace App\Filament\Resources\DiscoveryCollectionResource\Pages;

use App\Filament\Resources\DiscoveryCollectionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDiscoveryCollections extends ListRecords
{
    protected static string $resource = DiscoveryCollectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
