<?php

namespace App\Filament\Resources\DiscoveryCollectionResource\Pages;

use App\Filament\Resources\DiscoveryCollectionResource;
use App\Models\Business;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDiscoveryCollection extends EditRecord
{
    protected static string $resource = DiscoveryCollectionResource::class;

    protected function getHeaderActions(): array
    {
        // This is where your "Attach Businesses" action should go if you have it.
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // This method runs AFTER the record has been successfully saved.
    protected function afterSave(): void
    {
        // We leave this empty or remove it. 
        // The command now sets the path, and manual edits set the path.
        // There is no more logic conflict.
    }
}