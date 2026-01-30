<?php

namespace App\Filament\Resources\HeroSliderHistoryResource\Pages;

use App\Filament\Resources\HeroSliderHistoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHeroSliderHistory extends EditRecord
{
    protected static string $resource = HeroSliderHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
