<?php

namespace App\Filament\Resources\FacilityResource\Pages;

use App\Filament\Resources\FacilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Forms;

class EditFacility extends EditRecord
{
    protected static string $resource = FacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // <<< THIS IS THE CORRECTED ACTION >>>
            Action::make('attach_businesses')
                ->label('Attach Businesses with this Facility')
                ->icon('heroicon-o-building-storefront')
                ->form([
                    // The name 'businesses' must match the relationship name on your Facility model
                    Forms\Components\Select::make('businesses')
                        ->label('Select Businesses')
                        ->multiple()
                        ->relationship('businesses', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Adds this facility to the selected businesses.')
                        ->required(),
                ])
                ->action(function () {})
                ->successNotificationTitle('Businesses attached successfully!'),

            Actions\DeleteAction::make(),
        ];
    }
}