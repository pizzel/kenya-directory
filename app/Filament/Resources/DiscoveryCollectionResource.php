<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscoveryCollectionResource\Pages;
use App\Models\Business; // Import the Business model
use App\Models\DiscoveryCollection;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder; // Import the Builder
use Illuminate\Support\Str;

class DiscoveryCollectionResource extends Resource
{
    protected static ?string $model = DiscoveryCollection::class;
    protected static ?string $navigationIcon = 'heroicon-o-sparkles';
    protected static ?string $navigationGroup = 'Homepage Management';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn(Forms\Set $set, ?string $state) => $set('slug', Str::slug($state))),
                
                Forms\Components\TextInput::make('slug')
                    ->required()->maxLength(255)->unique(ignoreRecord: true),
                
                Forms\Components\Textarea::make('description')
                    ->columnSpanFull(),
                
                Forms\Components\FileUpload::make('cover_image_url')
                    ->label('Cover Image')->image()->disk('public')->directory('discovery-collections')
                    ->helperText('Optional. If left empty, a cover will be auto-assigned from an attached business.'),

                Forms\Components\Toggle::make('is_active')
                    ->label('Active on Homepage')->default(true),
                
                Forms\Components\TextInput::make('display_order')
                    ->numeric()->default(0),

                // <<< START OF THE NEW LOGIC >>>

                // STEP 1: Add a County filter. This field is NOT saved to the database.
                Forms\Components\Select::make('county_filter')
                    ->label('Filter Businesses by County (Optional)')
                    ->relationship('businesses.county', 'name') // Temporary relationship for options
                    ->multiple() // Allow selecting multiple counties to filter by
                    ->placeholder('Select one or more counties to filter the list below')
                    ->live() // This is crucial. It refreshes the form when this field changes.
                    ->dehydrated(false), // Tells Filament NOT to save this value.

                // STEP 2: Make the 'businesses' Select field reactive.
                Forms\Components\Select::make('businesses')
                    ->relationship(
                        name: 'businesses', 
                        titleAttribute: 'name',
                        // The core of the logic is here.
                        modifyQueryUsing: function (Builder $query, Forms\Get $get) {
                            // $get('county_filter') retrieves the current value of our new filter field.
                            $selectedCounties = $get('county_filter');

                            // If one or more counties have been selected in the filter...
                            if ($selectedCounties) {
                                // ...modify the query to only show businesses where the county_id is in the selected list.
                                return $query->whereIn('county_id', $selectedCounties);
                            }

                            // If no county is selected, return the query unmodified (show all businesses).
                            return $query;
                        }
                    )
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->helperText('Attach businesses to this collection. Use the filter above to narrow down choices.'),
                
                // <<< END OF THE NEW LOGIC >>>
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // 1. FIXED IMAGE COLUMN (No more checkFilePresence error)
                Tables\Columns\ImageColumn::make('cover_image_url')
                    ->label('Cover')
                    ->circular()
                    ->size(40)
                    ->disk('public') 
                    ->defaultImageUrl(asset('images/placeholder-card.jpg')),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('businesses_count')
                    ->counts('businesses')
                    ->label('Items')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Live')
                    ->boolean(),

                Tables\Columns\TextColumn::make('display_order')
                    ->label('Order')
                    ->sortable(),
            ])
            // RESTORED ACTIONS
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            // RESTORED BULK ACTIONS
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            // We eager load businesses so the form select box populates instantly
            ->with(['businesses']);
    }

    public static function getPages(): array
    {
        // ... this is unchanged ...
        return [
            'index' => Pages\ListDiscoveryCollections::route('/'),
            'create' => Pages\CreateDiscoveryCollection::route('/create'),
            'edit' => Pages\EditDiscoveryCollection::route('/{record}/edit'),
        ];
    }
}