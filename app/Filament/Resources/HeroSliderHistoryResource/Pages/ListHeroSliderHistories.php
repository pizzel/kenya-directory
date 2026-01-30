<?php

namespace App\Filament\Resources\HeroSliderHistoryResource\Pages;

use App\Filament\Resources\HeroSliderHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHeroSliderHistories extends ListRecords
{
    protected static string $resource = HeroSliderHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
