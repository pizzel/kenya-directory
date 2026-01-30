<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use App\Models\Business; // For linking
use App\Models\Event;    // For linking
use App\Models\User;     // For linking & admin relationship
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Carbon\Carbon;
// Import Filament Actions correctly
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
// No custom BulkAction needed here if default DeleteBulkAction is used

class ReportResource extends Resource
{
    protected static ?string $model = Report::class;
    protected static ?string $navigationIcon = 'heroicon-o-exclamation-triangle';
    protected static ?string $navigationGroup = 'Moderation';
    protected static ?string $navigationLabel = 'All User Reports';
    protected static ?string $pluralModelLabel = 'User Reports';
    protected static ?string $modelLabel = 'User Report';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        // This form schema is used by EditReport.php and ViewReport.php (if you have one)
        return $form
            ->schema([
                Forms\Components\Section::make('Report Details')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Placeholder::make('reported_item_info')
                            ->label('Reported Item')
                            ->content(function (?Report $record): string {
                                if (!$record) return 'N/A';
                                if ($record->business_id && $record->business) {
                                    return 'Business: ' . e($record->business->name);
                                } elseif ($record->event_id && $record->event) {
                                    return 'Event: ' . e($record->event->title);
                                }
                                return 'Item not found or type undetermined';
                            }),
                        Forms\Components\Placeholder::make('reporter_info')
                            ->label('Reported By')
                            ->content(function (?Report $record): string {
                                if (!$record) return 'N/A';
                                if ($record->user_id && $record->user) {
                                    return 'User: ' . e($record->user->name) . ' (ID: ' . $record->user_id . ')';
                                } elseif ($record->ip_address) {
                                    return 'Guest (IP: ' . e($record->ip_address) . ')';
                                }
                                return 'Unknown Reporter';
                            }),
                        Forms\Components\TextInput::make('report_reason')
                            ->formatStateUsing(fn ($state) => $state ? Str::title(str_replace('_', ' ', $state)) : '')
                            ->disabled()
                            ->label('Reason'),
                        Forms\Components\Placeholder::make('reported_at_info')
                            ->label('Reported On')
                            ->content(fn(?Report $record) => $record?->created_at ? $record->created_at->setTimezone(config('app.timezone'))->format('M j, Y H:i A') : 'N/A'),
                    ]),
                Forms\Components\Textarea::make('details')
                    ->label('User Provided Details')
                    ->columnSpanFull()
                    ->disabled()
                    ->rows(3),

                Forms\Components\Section::make('Moderation Action')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Review',
                                'reviewed_valid' => 'Reviewed - Valid Report (Action Taken/Needed)',
                                'reviewed_invalid' => 'Reviewed - Invalid Report (No Action)',
                                'resolved' => 'Resolved (Issue Addressed)',
                            ])
                            ->required()
                            ->default('pending')
                            ->live(),
                        Forms\Components\Textarea::make('admin_notes')
                            ->label('Moderator Notes')
                            ->helperText('Explain the action taken or why the report is valid/invalid.')
                            ->columnSpanFull()
                            ->rows(4),
                        // reviewed_by_admin_id and reviewed_at will be set in EditReport.php's mutateFormDataBeforeSave
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('reported_item_name_with_type')
                    ->label('Reported Item')
                    ->getStateUsing(function (Report $record): string {
                        if ($record->business_id && $record->business) {
                            return Str::limit($record->business->name, 30) . ' (Business)';
                        } elseif ($record->event_id && $record->event) {
                            return Str::limit($record->event->title, 30) . ' (Event)';
                        }
                        return 'N/A - Item Deleted?';
                    })
                    ->url(function (Report $record): ?string {
                        if ($record->business_id && $record->business) {
                            return BusinessResource::getUrl('edit', ['record' => $record->business_id]);
                        } elseif ($record->event_id && $record->event) {
                            return EventResource::getUrl('edit', ['record' => $record->event_id]);
                        }
                        return null;
                    })
                    ->openUrlInNewTab()
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->where(function (Builder $subQuery) use ($search) { // Group OR conditions
                                $subQuery->whereHas('business', fn (Builder $q) => $q->where('name', 'like', "%{$search}%"))
                                         ->orWhereHas('event', fn (Builder $q) => $q->where('title', 'like', "%{$search}%"));
                            });
                    })
                    ->sortable(false), // Sorting this composite column is complex, disable for now

                Tables\Columns\TextColumn::make('report_reason')
                    ->label('Reason')
                    ->formatStateUsing(fn (?string $state): string => $state ? Str::title(str_replace('_', ' ', $state)) : 'N/A')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Reporter')
                    ->searchable()
                    ->placeholder('Guest/Anonymous')
                    ->url(fn (Report $record): ?string => $record->user_id ? UserResource::getUrl('edit', ['record' => $record->user_id]) : null)
                    ->openUrlInNewTab(fn (Report $record): bool => (bool) $record->user_id)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('status')->badge()
                    ->formatStateUsing(fn ($state) => $state ? Str::title(str_replace('_', ' ', $state)) : 'N/A')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'resolved',
                        'danger'  => 'reviewed_valid',
                        'gray'    => 'reviewed_invalid',
                    ])->searchable()->sortable(),

                Tables\Columns\TextColumn::make('reviewedByAdmin.name')
                    ->label('Reviewed By')
                    ->placeholder('N/A')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reported On')
                    ->dateTime('M j, Y H:i')
                    ->timezone(config('app.timezone'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Reviewed On')
                    ->dateTime('M j, Y H:i')
                    ->timezone(config('app.timezone'))
                    ->placeholder('Not yet')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Review',
                        'reviewed_valid' => 'Reviewed - Valid',
                        'reviewed_invalid' => 'Reviewed - Invalid',
                        'resolved' => 'Resolved',
                    ])->label('Report Status'),
                SelectFilter::make('item_type')
                    ->label('Item Type')
                    ->options(['business' => 'Business', 'event' => 'Event'])
                    ->query(function (Builder $query, array $data): Builder {
                        if (empty($data['value'])) return $query; // No filter if 'All' or empty
                        if ($data['value'] === 'business') return $query->whereNotNull('business_id')->whereNull('event_id');
                        if ($data['value'] === 'event') return $query->whereNotNull('event_id')->whereNull('business_id');
                        return $query;
                    }),
                SelectFilter::make('business_id')->relationship('business', 'name')->label('Business')->searchable()->preload(),
                SelectFilter::make('event_id')->relationship('event', 'title')->label('Event')->searchable()->preload(),
                SelectFilter::make('user_id')->relationship('user', 'name')->label('Reporter (User)')->searchable()->preload(),

            ])
            ->actions([
                EditAction::make()->label('Review/Update Report'), // This will use Pages\EditReport and its mutateFormDataBeforeSave
                // ViewAction::make(), // Add if you create Pages\ViewReport
                DeleteAction::make()->visible(fn (Report $record) => auth()->user()->can('delete', $record)), // Policy controlled
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->visible(fn () => auth()->user()->can('deleteAny', Report::class)),
                    // Add custom bulk actions here if needed, e.g., bulk change status
                    // Example:
                    // Tables\Actions\BulkAction::make('mark_bulk_resolved')
                    //     ->label('Mark Selected as Resolved')
                    //     ->icon('heroicon-o-check-badge')->color('success')->requiresConfirmation()
                    //     ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                    //         $records->each(function (Report $report) {
                    //             if (auth()->user()->can('update', $report)) { // Check policy for each
                    //                 $report->status = 'resolved';
                    //                 $report->reviewed_by_admin_id = Auth::id();
                    //                 $report->reviewed_at = now();
                    //                 $report->save();
                    //             }
                    //         });
                    //         \Filament\Notifications\Notification::make()->title('Selected reports marked as resolved.')->success()->send();
                    //     })
                    //     ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // No relations usually managed directly from the global reports list
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReports::route('/'),
            // 'create' => Pages\CreateReport::route('/create'), // Admins do not create reports
            'edit' => Pages\EditReport::route('/{record}/edit'), // Used by EditAction to review/update
            // 'view' => Pages\ViewReport::route('/{record}'), // Add if you create a ViewReport page
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Admins/Editors do not create reports from the panel
    }

    // Optional: Modify the base query if needed, e.g., to always eager load relations
    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()->with(['business', 'event', 'user', 'reviewedByAdmin']);
    // }
}