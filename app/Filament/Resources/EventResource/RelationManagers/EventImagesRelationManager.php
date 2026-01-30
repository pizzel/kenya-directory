<?php

namespace App\Filament\Resources\EventResource\RelationManagers; // <<< CHECK NAMESPACE

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
// Add necessary imports for Intervention Image, Storage, Str if doing image handling here
use App\Models\EventImage; // Your image model for events
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;
use Intervention\Image\Typography\FontFactory;


class EventImagesRelationManager extends RelationManager // <<< CHECK CLASS NAME
{
    protected static string $relationship = 'images'; // Should match relationship name on Event model

    protected static ?string $recordTitleAttribute = 'file_path'; // Or 'caption' if you prefer

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('file_path')
                    ->label('Image File')
                    ->disk('public') // Use your public disk
                    ->directory(function (RelationManager $livewire) {
                        // Store images in a folder specific to the event
                        $event = $livewire->getOwnerRecord(); // Gets the parent Event model
                        return 'event_images/' . $event->id;
                    })
                    ->image() // Specifies it's an image upload
                    ->imageEditor() // Optional: enables Filament's basic image editor
                    ->imageResizeMode('cover')
                    ->imageCropAspectRatio('16:9') // Example aspect ratio
                    ->imageResizeTargetWidth('1280') // Resize to this width
                    ->imageResizeTargetHeight('720') // Resize to this height
                    ->required(fn (string $operation): bool => $operation === 'create') // Required on create
                    ->saveUploadedFileUsing(function (UploadedFile $file, callable $set, RelationManager $livewire) {
                        $event = $livewire->getOwnerRecord();
                        $siteName = config('app.name', 'Discover Kenya');
                        $businessNameForWatermark = $event->business->name; // Assuming Event has a 'business' relationship

                        $uuid = Str::uuid();
                        $extension = $file->getClientOriginalExtension();
                        $filename = $uuid . '.' . $extension;
                        $directory = 'event_images/' . $event->id;

                        // Read image using Intervention
                        $interventionImage = InterventionImage::read($file->getRealPath());

                        // Resize
                        $interventionImage->cover(1280, 720); // Match target dimensions

                        // Watermarks
                        $imageWidth = $interventionImage->width();
                        $imageHeight = $interventionImage->height();
                        $fontSize = round($imageHeight / 35);
                        $padding = round($imageWidth / 40);

                        // Site Name Watermark
                        $interventionImage->text($siteName, $padding, $imageHeight - $padding, function (FontFactory $font) use ($fontSize) {
                            $font->file(public_path('fonts/arial.ttf'));
                            $font->size($fontSize); $font->color('rgba(255, 255, 255, 0.6)');
                            $font->align('left'); $font->valign('bottom');
                        });
                        // Business Name Watermark for the event
                        $interventionImage->text($businessNameForWatermark, $imageWidth - $padding, $imageHeight - $padding, function (FontFactory $font) use ($fontSize) {
                            $font->file(public_path('fonts/arial.ttf'));
                            $font->size($fontSize); $font->color('rgba(255, 255, 255, 0.6)');
                            $font->align('right'); $font->valign('bottom');
                        });

                        // Save the processed image
                        $storedPath = Storage::disk('public')->putFileAs($directory, new \Illuminate\Http\File($interventionImage->save()->filePath()), $filename);
                        // $storedPath = $interventionImage->save(Storage::disk('public')->path($directory . '/' . $filename));


                        return $storedPath; // Return the path relative to the disk's root
                    }),

                Forms\Components\TextInput::make('caption')
                    ->maxLength(255),
                Forms\Components\Toggle::make('is_main_event_image')
                    ->label('Set as Main Event Image')
                    ->helperText('Only one image can be the main one. Setting this will unset others.')
                    ->afterStateUpdated(function (bool $state, callable $set, ?Model $record, RelationManager $livewire) {
                        if ($state === true) {
                            // Unset other main images for this event
                            $event = $livewire->getOwnerRecord();
                            $event->images()->whereNot('id', $record?->id)->update(['is_main_event_image' => false]);
                        }
                    }),
                Forms\Components\TextInput::make('order')
                    ->numeric()->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('caption') // Or file_path
            ->columns([
                Tables\Columns\ImageColumn::make('file_path')
                    ->label('Image')
                    ->disk('public') // Specify the disk
                    ->height(60),
                Tables\Columns\TextColumn::make('caption')->limit(50)->searchable(),
                Tables\Columns\IconColumn::make('is_main_event_image')->boolean()->label('Main'),
                Tables\Columns\TextColumn::make('order')->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->reorderable('order'); // Allow reordering based on 'order' column
    }
}