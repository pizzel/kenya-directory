<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EventResource\Pages;
use App\Filament\Resources\EventResource\RelationManagers;
use App\Models\Event;
use App\Models\Business;
use App\Models\User;
use App\Models\County;
use App\Models\EventCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Resources\Components\Tab; // For tabs on list page
// Import Filament Actions correctly
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ForceDeleteAction; // <<< CORRECT IMPORT
use Filament\Tables\Actions\RestoreAction;   // <<< CORRECT IMPORT
use Filament\Tables\Actions\ViewAction; 
// Import Filament BULK Actions correctly
use Filament\Tables\Actions\BulkActionGroup;      // <<< ENSURE THIS
use Filament\Tables\Actions\DeleteBulkAction;     // <<< ENSURE THIS
use Filament\Tables\Actions\ForceDeleteBulkAction; // <<< CORRECT IMPORT
use Filament\Tables\Actions\RestoreBulkAction; 
use Filament\Tables\Actions\BulkAction;


class EventResource extends Resource
{
    protected static ?string $model = Event::class;

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationGroup = 'Business Management'; // Or 'Content Moderation' or 'Events'
    protected static ?string $navigationLabel = 'Manage Events';
    protected static ?int $navigationSort = 4; // Adjust as needed

    public static function form(Form $form): Form
    {
        // Admin form to edit events
        return $form
            ->schema([
                Forms\Components\Section::make('Event Details')->columns(2)->schema([
                    Forms\Components\Select::make('business_id')
                        ->relationship('business', 'name')->required()->searchable()->preload()
                        ->label('Organizing Business'),
                    Forms\Components\Select::make('user_id') // The business owner who created it
                        ->relationship('user', 'name')->required()->searchable()->preload()
                        ->label('Submitted By (Owner)')->disabledOn('edit'), // Typically set on creation
                    Forms\Components\TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                    Forms\Components\RichEditor::make('description')->required()->columnSpanFull(),
                    Forms\Components\DateTimePicker::make('start_datetime')->required()->seconds(false)->native(false)->timezone(config('app.timezone')),
                    Forms\Components\DateTimePicker::make('end_datetime')->required()->seconds(false)->native(false)->timezone(config('app.timezone'))->after('start_datetime'),
                ]),
                Forms\Components\Section::make('Location & Logistics')->columns(2)->schema([
                    Forms\Components\Select::make('county_id')->relationship('county', 'name')->required()->searchable()->preload(),
                    Forms\Components\TextInput::make('address')->maxLength(255),
                    Forms\Components\TextInput::make('latitude')->numeric()->nullable(),
                    Forms\Components\TextInput::make('longitude')->numeric()->nullable(),
                    Forms\Components\Toggle::make('is_free')->label('Free Event?')->reactive()
                        ->default(false),
                    Forms\Components\TextInput::make('price')->numeric()->prefix('Ksh')->nullable()
                        ->visible(fn (Forms\Get $get) => !$get('is_free')), // Show if not free
                    Forms\Components\TextInput::make('ticketing_url')->url()->nullable()->columnSpanFull(),
                ]),
                Forms\Components\Section::make('Categorization & Status')->columns(2)->schema([
                    Forms\Components\Select::make('event_categories') // For the many-to-many relationship
                        ->multiple()
                        ->relationship(name: 'categories', titleAttribute: 'name') // name of relationship, display attribute
                        ->preload()->searchable()->required()->label('Event Activities'),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending_approval' => 'Pending Approval',
                            'active' => 'Active',
                            'cancelled' => 'Cancelled',
                            'past' => 'Past', // Events can become 'past' automatically via a scheduled task too
                        ])->required()->default('pending_approval'),
                ]),
                // Image management will be via a Relation Manager
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Add ImageColumn for main event image if EventImage model and relation exist
                Tables\Columns\TextColumn::make('title')->searchable()->sortable()->limit(40),
                Tables\Columns\TextColumn::make('business.name')->label('Organizer')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('start_datetime')->dateTime('M j, Y H:i')->sortable()->timezone(config('app.timezone')),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->colors([
                        'warning' => 'pending_approval',
                        'success' => 'active',
                        'danger' => 'cancelled',
                        'gray' => 'past',
                    ])->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.name')->label('Submitted By')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options([/* status options */]),
                SelectFilter::make('business_id')->relationship('business', 'name')->label('Business')->searchable()->preload(),
                SelectFilter::make('county_id')->relationship('county', 'name')->label('County')->searchable()->preload(),
                Tables\Filters\TrashedFilter::make(), // If Event model uses SoftDeletes
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
				Action::make('mark_as_past')
					->label('Mark as Past')
					->icon('heroicon-o-archive-box')
					->color('gray')
					->requiresConfirmation()
					->action(fn (Event $record) => $record->update(['status' => 'past']))
					->visible(fn (Event $record): bool => $record->status === 'active' && $record->end_datetime->isPast()), // Only show if active but end date passed
                Action::make('approve_event')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')->color('success')->requiresConfirmation()
                    ->action(fn (Event $record) => $record->update(['status' => 'active']))
                    ->visible(fn (Event $record): bool => $record->status === 'pending_approval'),
                Action::make('cancel_event')
                    ->label('Cancel Event')
                    ->icon('heroicon-o-x-circle')->color('danger')->requiresConfirmation()
                    ->action(fn (Event $record) => $record->update(['status' => 'cancelled']))
                    ->visible(fn (Event $record): bool => $record->status === 'active' || $record->status === 'pending_approval'),
                Tables\Actions\DeleteAction::make(), // Will use SoftDeletes if model has it
                ForceDeleteAction::make(), // For hard delete
                RestoreAction::make(),   // For restoring
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    BulkAction::make('approve_selected')
                        ->label('Approve Selected')->icon('heroicon-o-check-circle')->color('success')
                        ->action(fn (\Illuminate\Database\Eloquent\Collection $records) => $records->filter(fn($r) => $r->status === 'pending_approval')->each->update(['status' => 'active'])),
                ]),
            ])
            ->defaultSort('start_datetime', 'asc');
            // ->tabs([ // Add tabs for easy filtering
                // Tab::make('All Events')
                    // ->badge(Event::query()->count()), // Modify if using SoftDeletes
                // Tab::make('Upcoming & Active')
                    // ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active')->where('end_datetime', '>=', now()))
                    // ->badge(Event::where('status', 'active')->where('end_datetime', '>=', now())->count()),
                // Tab::make('Pending Approval')
                    // ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending_approval'))
                    // ->badge(Event::where('status', 'pending_approval')->count())
                    // ->icon('heroicon-o-clock'),
                // Tab::make('Past Events')
                    // ->modifyQueryUsing(fn (Builder $query) => $query->where('end_datetime', '<', now())->orWhere('status', 'past'))
                    // ->badge(Event::where('end_datetime', '<', now())->orWhere('status', 'past')->count()),
                // Tab::make('Cancelled')
                    // ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled'))
                    // ->badge(Event::where('status', 'cancelled')->count()),
            // ]);
    }

    public static function getRelations(): array
    {
        return [
          // RelationManagers\EventImagesRelationManager::class, // You will create this
		  RelationManagers\ReportsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEvents::route('/'),
            'create' => Pages\CreateEvent::route('/create'), // Admins can create events
            'edit' => Pages\EditEvent::route('/{record}/edit'),
        ];
    }

    // Add Policy based authorization
    // public static function canViewAny(): bool { return auth()->user()->isAdminOrEditor(); }
}