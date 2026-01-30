<?php

namespace App\Filament\Resources\TagResource\Pages;

use App\Filament\Resources\TagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Forms;

class EditTag extends EditRecord
{
    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // <<< THIS IS THE CORRECTED ACTION >>>
            Action::make('attach_businesses')
                ->label('Attach Businesses with this Tag')
                ->icon('heroicon-o-building-storefront')
                ->form([
                    // The name 'businesses' must match the relationship name on your Tag model
                    Forms\Components\Select::make('businesses')
                        ->label('Select Businesses')
                        ->multiple()
                        ->relationship('businesses', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('Adds this tag to the selected businesses.')
                        ->required(),
                ])
                ->action(function () {})
                ->successNotificationTitle('Businesses attached successfully!'),

            Actions\DeleteAction::make(),
        ];
    }
}