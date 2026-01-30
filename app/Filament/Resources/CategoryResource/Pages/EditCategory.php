<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Forms;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // <<< THIS IS THE CORRECTED ACTION >>>
            Action::make('attach_businesses')
                ->label('Attach Businesses to this Activity')
                ->icon('heroicon-o-building-storefront')
                ->form([
                    // Tell the Select field which relationship to manage
                    Forms\Components\Select::make('businesses')
                        ->label('Select Businesses to Add')
                        ->multiple()
                        ->relationship('businesses', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Adds the selected businesses to this activity.')
                        ->required(),
                ])
                // The action is now empty because Filament handles the relationship automatically
                ->action(function () {})
                ->successNotificationTitle('Businesses attached successfully!'),
            
            Actions\DeleteAction::make(),
        ];
    }
}