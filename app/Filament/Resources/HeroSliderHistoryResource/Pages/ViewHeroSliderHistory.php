<?php

namespace App\Filament\Resources\HeroSliderHistoryResource\Pages;

use App\Filament\Resources\HeroSliderHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewHeroSliderHistory extends ViewRecord
{
    protected static string $resource = HeroSliderHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
