<?php

namespace App\Filament\Resources;

use App\Filament\Resources\HeroSliderHistoryResource\Pages;
// Make sure RelationManagers namespace is correctly referenced if you ever add one here, though unlikely for this resource.
// use App\Filament\Resources\HeroSliderHistoryResource\RelationManagers;
use App\Models\HeroSliderHistory;
use App\Models\Business; // For linking to BusinessResource in the table
use App\Models\User;    // For displaying admin name
use Filament\Forms\Components\Grid; // <<< ENSURE THIS IS PRESENT AND CORRECT
use Filament\Forms\Components\Section; // You likely need this too if using sections in the form
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope; // Only if HeroSliderHistory model uses SoftDeletes
use Carbon\Carbon;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth; // For default admin_id in form (though create is disabled)

class HeroSliderHistoryResource extends Resource
{
    protected static ?string $model = HeroSliderHistory::class;

    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-line'; // Or 'heroicon-o-calendar-days'
    protected static ?string $navigationLabel = 'Hero Slider Scheduless'; // Your desired label
    protected static ?string $pluralModelLabel = 'Hero Slider Placements';
    protected static ?string $modelLabel = 'Hero Slider Placement';

    // Assign to a group defined in AdminPanelProvider.php
    // Example: 'Business Management' or 'Feature Management'
    protected static ?string $navigationGroup = 'Business Management';
    protected static ?int $navigationSort = 3; // Adjust order within the group

    public static function form(Form $form): Form
    {
        // This form is for VIEWING or EDITING an existing placement.
        // Direct CREATION via this resource page is disabled by canCreate().
        return $form
            ->schema([
                Forms\Components\Section::make('Placement Details')
                    ->schema([
                        Forms\Components\Select::make('business_id')
                            ->relationship('business', 'name')
                            ->searchable(['name', 'slug']) // Search business by name or slug
                            ->preload()
                            ->required()
                            ->disabled() // Usually disabled on edit/view for a history record
                            ->label('Business'),
                        Forms\Components\Grid::make(2)->schema([
                            Forms\Components\DateTimePicker::make('activated_at')
                                ->label('Placement Starts At')
                                ->required()
                                ->seconds(false)
                                ->native(false), // Use Filament's picker
                            Forms\Components\DateTimePicker::make('set_to_expire_at')
                                ->label('Placement Expires At')
                                ->required()
                                ->seconds(false)
                                ->native(false)
                                ->minDate(fn (Forms\Get $get) => $get('activated_at') ? Carbon::parse($get('activated_at'))->addMinutes(5) : now()->addMinutes(5)), // Expiry must be after activation
                        ]),
                    ])->columns(1), // Section for main details

                Forms\Components\Section::make('Payment & Package Information')
                    ->schema([
                        Grid::make(2)->schema([
                            Forms\Components\TextInput::make('package_name')
                                ->maxLength(255)
                                ->placeholder('e.g., Launch Offer, Premium'),
                            Forms\Components\TextInput::make('amount_paid')
                                ->numeric()
                                ->prefix('Ksh')
                                ->nullable(),
                        ]),
                        Forms\Components\TextInput::make('payment_reference')
                            ->maxLength(255)
                            ->placeholder('e.g., M-Pesa Code, INV-123'),
                    ])->collapsible(),

                Forms\Components\Section::make('Administrative Details')
                    ->schema([
                        Forms\Components\Select::make('admin_id')
                            ->relationship('admin', 'name') // Assumes 'name' on User model
                            ->label('Processed By Admin')
                            ->searchable()
                            ->preload()
                            ->disabled() // Usually not changed after creation
                            ->default(Auth::id()), // Should be set on creation
                        Forms\Components\Textarea::make('notes')
                            ->label('Admin Notes')
                            ->columnSpanFull()
                            ->rows(3),
                    ])->collapsible()->collapsed(), // Start collapsed
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('business.name')
                    ->searchable()
                    ->sortable()
                    ->url(fn (HeroSliderHistory $record): string =>
                        // Ensure BusinessResource exists and is correctly namespaced
                        \App\Filament\Resources\BusinessResource::getUrl('edit', ['record' => $record->business_id])
                    )
                    ->label('Business'),
                Tables\Columns\TextColumn::make('activated_at')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->label('Starts'),
                Tables\Columns\TextColumn::make('set_to_expire_at')
                    ->dateTime('M j, Y H:i')
                    ->sortable()
                    ->label('Expires')
                    ->color(function (HeroSliderHistory $record) {
                        if ($record->set_to_expire_at->isPast()) return 'danger'; // Expired
                        if ($record->activated_at->isFuture()) return 'warning'; // Upcoming/Scheduled
                        if ($record->activated_at->lte(now()) && $record->set_to_expire_at->gte(now())) return 'success'; // Currently Active
                        return null;
                    })
                    ->description(function(HeroSliderHistory $record) {
                        if ($record->set_to_expire_at->isPast()) return 'Expired';
                        if ($record->activated_at->isFuture()) return 'Scheduled for '. $record->activated_at->diffForHumans();
                        if ($record->activated_at->lte(now()) && $record->set_to_expire_at->gte(now())) return 'Currently Active';
                        return '';
                    }),
                Tables\Columns\TextColumn::make('package_name')
                    ->searchable()
                    ->placeholder('N/A')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('amount_paid')
                    ->money('kes') // Ensure your app's default currency is KES or configure
                    ->sortable()
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('admin.name')
                    ->label('Set By')
                    ->searchable()
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Record Date')
                    ->dateTime('M j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('business_id') // Corrected to use actual column name
                    ->relationship('business', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Business'),
                Filter::make('currently_active')
                    ->label('Currently Active')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('activated_at', '<=', now())
                        ->where('set_to_expire_at', '>=', now())),
                Filter::make('expired')
                    ->label('Expired')
                    ->query(fn (Builder $query): Builder => $query->where('set_to_expire_at', '<', now())),
                Filter::make('upcoming')
                    ->label('Upcoming')
                    ->query(fn (Builder $query): Builder => $query->where('activated_at', '>', now())),
                Tables\Filters\Filter::make('activated_at_range') // Date range filter
                    ->form([
                        Forms\Components\DatePicker::make('activated_from')->label('Activated From'),
                        Forms\Components\DatePicker::make('activated_until')->label('Activated Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['activated_from'], fn (Builder $query, $date): Builder => $query->whereDate('activated_at', '>=', $date))
                            ->when($data['activated_until'], fn (Builder $query, $date): Builder => $query->whereDate('activated_at', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // Tables\Actions\EditAction::make()
                    // // Optionally restrict who can edit history records via policy
                    // ->visible(fn (HeroSliderHistory $record) => auth()->user()->can('update', $record)),
				Tables\Actions\EditAction::make()
						->visible(fn (HeroSliderHistory $record) => auth()->user()->can('update', $record)) // Let policy primarily handle visibility
						->disabled(function (HeroSliderHistory $record): bool { // But add visual disable for editors on expired
							if (auth()->user()->isEditor()) {
								$expiresAt = ($record->set_to_expire_at instanceof Carbon)
									? $record->set_to_expire_at
									: Carbon::parse($record->set_to_expire_at);
								return $expiresAt->isPast();
							}
							return false;
						})
						->tooltip(function (HeroSliderHistory $record): ?string {
							if (auth()->user()->isEditor()) {
								$expiresAt = ($record->set_to_expire_at instanceof Carbon)
									? $record->set_to_expire_at
									: Carbon::parse($record->set_to_expire_at);
								if ($expiresAt->isPast()) {
									return 'Expired placements can only be edited by a Super Admin.';
								}
							}
							return null;
						}),	

                Tables\Actions\DeleteAction::make()
                    // Optionally restrict who can delete history records via policy
                    ->visible(fn (HeroSliderHistory $record) => auth()->user()->can('delete', $record)),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        // Optionally restrict who can bulk delete
                        ->visible(fn () => auth()->user()->can('deleteAny', HeroSliderHistory::class)),
                ]),
            ])
            ->defaultSort('activated_at', 'desc');
    }

    public static function getRelations(): array
    {
        // This resource typically won't manage other relations itself.
        // It is a "history" or "log" type table.
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListHeroSliderHistories::route('/'),
            // Creation is handled via the BusinessResource's HeroSliderHistoriesRelationManager
            // So, no 'create' page directly on this resource.
            // 'create' => Pages\CreateHeroSliderHistory::route('/create'),
            'view' => Pages\ViewHeroSliderHistory::route('/{record}'),
            'edit' => Pages\EditHeroSliderHistory::route('/{record}/edit'),
        ];
    }

    /**
     * Prevent creation of new HeroSliderHistory records directly from this resource's list page.
     * New placements should be created via the RelationManager on the BusinessResource edit page.
     */
    public static function canCreate(): bool
    {
        return false;
    }

    // If you want to apply global scopes, like only showing non-soft-deleted businesses in selects
    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()->withoutGlobalScopes([
    //         SoftDeletingScope::class,
    //     ]);
    // }
}