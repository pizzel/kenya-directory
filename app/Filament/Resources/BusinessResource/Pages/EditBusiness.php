<?php

namespace App\Filament\Resources\BusinessResource\Pages;

use App\Filament\Resources\BusinessResource;
use Filament\Actions; // Keep this for DeleteAction and generic Action
use Filament\Resources\Pages\EditRecord;

class EditBusiness extends EditRecord
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Custom "Save Changes" Action that calls the page's save method
            Actions\Action::make('save') // Give it a unique name like 'save' or 'updateRecord'
                ->label('Save Changes')
                ->action('save') // This tells Filament to call the 'save' method on this Page class
                                 // The 'save' method is inherited from EditRecord and handles form submission
                ->color('primary') // Optional: make it look like a primary action
                ->icon('heroicon-o-check-circle'), // Optional icon

            // Default Delete Action
            Actions\DeleteAction::make(),
        ];
    }

    // Optional: Customize redirect after saving
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index'); // Redirect to the list page
    }

    // Optional: Customize success notification
    // The default is usually "Saved"
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Business updated successfully!';
    }

    // Optional: Customize the default save action button label at the bottom of the form
    // protected function getFormActions(): array
    // {
    //     return [
    //         $this->getSaveFormAction()->label('Save Business Changes Below'), // Customizes the main save button at the bottom
    //         $this->getCancelFormAction(),
    //     ];
    // }
}