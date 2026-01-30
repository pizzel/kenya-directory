<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BusinessResource\Pages;
use App\Filament\Resources\BusinessResource\RelationManagers;
use App\Models\Business;
use App\Models\User;
use App\Models\Tag;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Illuminate\Support\Str;

class BusinessResource extends Resource
{
    protected static ?string $model = Business::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?string $navigationGroup = 'Business Management';
    protected static ?int $navigationSort = 1;

    public static function getRelations(): array
    {
        return [
            RelationManagers\SchedulesRelationManager::class,
            RelationManagers\MediaRelationManager::class,
            RelationManagers\ReviewsRelationManager::class,
            RelationManagers\HeroSliderHistoriesRelationManager::class,
            RelationManagers\ReportsRelationManager::class,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(3)->schema([
                    Section::make('Core Business Information')
                        ->description('Basic details about the business.')
                        ->schema([
                            Select::make('user_id')
                                ->label('Business Owner')
                                ->relationship('owner', 'name')
                                ->searchable(['name', 'email'])
                                ->preload()
                                ->required()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                    Forms\Components\TextInput::make('email')->email()->required()->unique(table: User::class, column: 'email', ignoreRecord: true),
                                    Forms\Components\TextInput::make('password')->password()->required()->confirmed()->minLength(8),
                                    Forms\Components\TextInput::make('password_confirmation')->password()->required(),
                                    Select::make('role')->options(['business_owner' => 'Business Owner', 'user' => 'Regular User'])->default('business_owner')->required(),
                                    Forms\Components\Toggle::make('email_verified_at')->label('Email Verified')->default(true)->formatStateUsing(fn ($state) => (bool)$state)->dehydrateStateUsing(fn ($state) => $state ? now() : null),
                                ]),
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255)
                                ->live(onBlur: true)
                                ->afterStateUpdated(fn (Forms\Set $set, ?string $state, ?Business $record) => $set('slug', $record && $record->name === $state ? $record->slug : Str::slug($state))),
                            Forms\Components\TextInput::make('slug')
                                ->required()
                                ->unique(table: Business::class, column: 'slug', ignoreRecord: true)
                                ->maxLength(255),
                            
                            // 1. ADDED: Tags Input (You had the model imported but no input)
                            Select::make('tags')
                                ->relationship('tags', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                ]),

                            Forms\Components\RichEditor::make('about_us')
                                ->label('About Us (Short Intro)')
                                ->nullable()
                                ->columnSpanFull(),

                            Forms\Components\RichEditor::make('description')
                                ->label('Full Description (Optional)')
                                ->nullable()
                                ->columnSpanFull(),
                        ])->columnSpan(2),

                    Section::make('Administrative Controls')
                        ->schema([
                            Forms\Components\Toggle::make('is_verified')->label('Verified Listing')->onColor('success')->offColor('danger'),
                            Select::make('status')
                                ->options([ 'pending_approval' => 'Pending Approval', 'active' => 'Active', 'delisted' => 'Delisted', 'closed_permanently' => 'Closed Permanently', 'closed_by_reports' => 'Closed by Reports' ])
                                ->required()
                                ->default('pending_approval'),
                            
                            // If you want to EDIT views manually, remove ->disabled(). 
                            // kept disabled for safety, but data is visible.
                            Forms\Components\TextInput::make('views_count')
                                ->label('View Count')
                                ->numeric()
                                ->default(0)
                                ->disabled(), 
                        ])->columnSpan(1),
                ]),

                Section::make('Promotional Status')
                    ->schema([
                        Forms\Components\Toggle::make('is_featured')->label('Featured Listing'),
                        Forms\Components\DateTimePicker::make('featured_expires_at')->label('Feature Expires At'),
                    ])->collapsible(),

                Section::make('Location & Contact Details')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\Textarea::make('address')->required()->columnSpanFull()->rows(3),
                            
                            Select::make('county_id')
                                ->label('County')
                                ->relationship('county', 'name')
                                ->searchable()
                                ->preload()
                                ->required(),
                            
                            // 2. ADDED: Coordinates (You were selecting them in Query, but couldn't edit them)
                            Forms\Components\TextInput::make('latitude')
                                ->numeric()
                                ->inputMode('decimal'),
                            Forms\Components\TextInput::make('longitude')
                                ->numeric()
                                ->inputMode('decimal'),

                            Forms\Components\TextInput::make('phone_number')->tel(),
                            Forms\Components\TextInput::make('email')->email(),
                            Forms\Components\TextInput::make('website')->url()->columnSpanFull(),
                        ]),
                    ])->collapsible(),

                Section::make('Activities & Facilities')
                     ->schema([
                        Grid::make(2)->schema([
                            Select::make('categories')
                                ->multiple()
                                ->relationship('categories', 'name')
                                ->preload()
                                ->searchable()
                                ->label('Activities'),
                            CheckboxList::make('facilities')
                                ->relationship('facilities', 'name')
                                ->columns(2)
                                ->label('Facilities'),
                        ]),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->columns([
                Tables\Columns\ImageColumn::make('main_image_url')
                    ->label('Img')
                    ->circular()
                    ->size(35)
                    // Ensure your Business model has 'media' relationship and Spatie media library set up
                    ->getStateUsing(fn (Business $record): ?string => $record->media->firstWhere('collection_name', 'images')?->getUrl('thumbnail'))
                    ->defaultImageUrl(asset('images/placeholder-card.jpg')),

                Tables\Columns\TextColumn::make('name')->searchable()->sortable()->limit(25),

                // This column requires 'description' to be selected in getEloquentQuery
                Tables\Columns\IconColumn::make('description')
                    ->label('D.')
                    ->icon(fn (?string $state): string => !empty($state) ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                    ->color(fn (?string $state): string => !empty($state) ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('owner.name')->label('Owner')->searchable()->sortable()->toggleable(),
                Tables\Columns\ToggleColumn::make('is_verified')->label('Ver.')->sortable(),
                Tables\Columns\TextColumn::make('status')->badge()->sortable(),
                Tables\Columns\ToggleColumn::make('is_featured')->label('Feat.')->sortable(),
                Tables\Columns\TextColumn::make('pending_reports_count')->label('Reports')->badge()->color('danger')->sortable(),
                Tables\Columns\TextColumn::make('views_count')->label('Views')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Added')->dateTime('M j')->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_featured')->label('Featured'),
                SelectFilter::make('status')->options(['active' => 'Active', 'pending_approval' => 'Pending']),
                SelectFilter::make('county')->relationship('county', 'name')->searchable()->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Action::make('preview')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(fn (Business $record): string => route('listings.show', $record->slug)) // Ensure this route exists
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->withTrashed()
            ->with(['owner:id,name', 'county:id,name', 'media'])
            ->withCount(['reports as pending_reports_count' => function (Builder $query) {
                $query->where('status', 'pending');
            }]);

        $currentRoute = request()->route()?->getName();

        // Optimize only for the Index (Table) page
        if ($currentRoute && str_ends_with($currentRoute, '.index')) {
            $query->select([
                'businesses.id',
                'businesses.user_id',
                'businesses.name',
                'businesses.slug',
                'businesses.county_id',
                'businesses.is_verified',
                'businesses.status',
                'businesses.is_featured',
                'businesses.views_count',
                'businesses.created_at',
                'businesses.deleted_at',
                // Added description so the Table IconColumn works
                'businesses.description', 
            ]);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBusinesses::route('/'),
            'create' => Pages\CreateBusiness::route('/create'),
            'edit' => Pages\EditBusiness::route('/{record}/edit'),
        ];
    }
}