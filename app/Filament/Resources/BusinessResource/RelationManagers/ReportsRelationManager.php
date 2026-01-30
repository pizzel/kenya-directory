<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Report; // Your Report model
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkAction;         // For custom bulk actions
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Collection;              // For type hinting $records in bulk action
use Filament\Notifications\Notification;        // For success notification

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';
    protected static ?string $recordTitleAttribute = 'report_reason'; // Or an accessor for a better title

    protected static ?string $navigationLabel = 'User Reports'; // Not used for Relation Manager direct nav
    protected static ?string $pluralModelLabel = 'User Reports for this Business';
    protected static ?string $modelLabel = 'User Report';


    public function form(Form $form): Form
    {
        // This form is used by EditAction to review/process a single report
        return $form
            ->schema([
                Forms\Components\Section::make('Report Information')
                    ->description('Details of the report submitted by the user.')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'name')->disabled()->label('Reported By'),
                        Forms\Components\TextInput::make('ip_address')->disabled()->label('Reporter IP (if guest)'),
                        Forms\Components\TextInput::make('report_reason')
                            ->formatStateUsing(fn ($state) => $state ? Str::title(str_replace('_', ' ', $state)) : 'N/A')
                            ->disabled()->label('Reason'),
                        Forms\Components\Textarea::make('details')->label('User Provided Details')->columnSpanFull()->disabled()->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('Moderation Action')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending Review',
                                'reviewed_valid' => 'Reviewed - Valid Report (Action Taken/Needed)',
                                'reviewed_invalid' => 'Reviewed - Invalid Report (No Action)',
                                'resolved' => 'Resolved (Issue Addressed)',
                            ])->required()->live(),
                        Forms\Components\Textarea::make('admin_notes')->label('Your Moderation Notes')->columnSpanFull()->rows(4)
                            ->helperText('Explain the action taken or why the report is valid/invalid.'),
                        // reviewed_by_admin_id and reviewed_at will be set on save by mutateFormDataUsing
                    ])->columns(1),
            ]);
    }

	public function table(Table $table): Table // Not static
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_reason')
                    ->label('Reason')
                    ->formatStateUsing(function (?string $state): string { // <<< CHANGED to ?string $state
                        if (is_null($state)) {
                            return 'N/A'; // Or 'Reason Not Specified'
                        }
                        return Str::title(str_replace('_', ' ', $state));
                    })
                    ->searchable()
                    ->sortable()
                    ->description(fn (Report $record): ?string => $record->details ? Str::limit($record->details, 40) : null), // Allow null description

                Tables\Columns\TextColumn::make('details')
                    ->limit(50)
                    ->tooltip(fn(Report $record): ?string => $record->details) // Allow null tooltip
                    ->placeholder('No details provided.') // Placeholder for empty/null details
                    ->toggleable(isToggledHiddenByDefault: true), // Maybe hide details by default

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Reporter')
                    ->searchable()
                    ->placeholder('Guest/Anonymous')
					 ->url(function (Report $record): ?string {
						if ($record->user_id) {
							// Make sure UserResource is correctly namespaced and exists
							return \App\Filament\Resources\UserResource::getUrl('edit', ['record' => $record->user_id]);
						}
						return null; 
					})
					->openUrlInNewTab(fn (Report $record): bool => (bool) $record->user_id) // Open in new tab only if it's a link
                    ->toggleable(),
                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('status')->badge()
                    ->colors([
							'warning' => 'pending',
							'success' => fn ($state) => $state === 'resolved',
							'danger' => fn ($state) => $state === 'reviewed_valid',
							'gray' => fn ($state) => $state === 'reviewed_invalid',
							'secondary' => fn ($state) => !in_array($state, ['pending', 'resolved', 'reviewed_valid', 'reviewed_invalid']),
						])->searchable()->sortable(),
                Tables\Columns\TextColumn::make('reviewedByAdmin.name')
                    ->label('Reviewed By')->placeholder('N/A')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('reviewed_at')
                    ->label('Reviewed On')->dateTime('M j, Y H:i')->timezone(config('app.timezone'))
                    ->placeholder('Not yet')->sortable()->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Reported On')->dateTime('M j, Y H:i')->timezone(config('app.timezone'))
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending Review',
                        'reviewed_valid' => 'Reviewed - Valid',
                        'reviewed_invalid' => 'Reviewed - Invalid',
                        'resolved' => 'Resolved',
                    ])
                    ->label('Report Status'),
                SelectFilter::make('user_id') // Filter by reporter user
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Reported By User'),
            ])
            ->headerActions([
                // No 'Create' action in the header of this relation manager table,
                // as reports are created by users on the frontend.
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Review/Process')
                    ->icon('heroicon-o-pencil-square')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['reviewed_by_admin_id'] = Auth::id();
                        $data['reviewed_at'] = now();
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(), // Allows deleting a single report record
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    BulkAction::make('mark_as_resolved')
                        ->label('Mark Selected as Resolved')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Mark Reports as Resolved')
                        ->modalDescription('Are you sure you want to mark the selected reports as resolved? This will also record you as the reviewer and the current time.')
                        ->action(function (Collection $records) {
                            $records->each(function (Report $report) {
                                $report->status = 'resolved';
                                $report->reviewed_by_admin_id = Auth::id();
                                $report->reviewed_at = now();
                                $report->save();
                            });
                            Notification::make()
                                ->title($records->count() . ' ' . Str::plural('report', $records->count()) . ' marked as resolved.')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
						
					// NEW BULK ACTION: Mark as Valid
						BulkAction::make('mark_as_valid')
							->label('Mark Selected as Valid')
							->icon('heroicon-o-check-circle') // Similar to resolved, or a different check icon
							->color('danger') // Or another appropriate color
							->requiresConfirmation()
							->modalHeading('Mark Reports as Valid')
							->modalDescription('Are you sure you want to mark the selected reports as "Reviewed - Valid"? This indicates the report was legitimate. Further action on the reported business might be needed separately.')
							->action(function (Collection $records) {
								$records->each(function (Report $report) {
									$report->status = 'reviewed_valid'; // Set the new status
									$report->reviewed_by_admin_id = Auth::id();
									$report->reviewed_at = now();
									$report->save();
								});
								Notification::make()
									->title($records->count() . ' ' . Str::plural('report', $records->count()) . ' marked as valid.')
									->success() // Or ->info()
									->send();
							})
							->deselectRecordsAfterCompletion(),
				// NEW BULK ACTION: Mark as InValid
						BulkAction::make('reviewed_invalid')
							->label('Mark Selected as InValid')
							->icon('heroicon-o-check-circle') // Similar to resolved, or a different check icon
							->color('gray') // Or another appropriate color
							->requiresConfirmation()
							->modalHeading('Mark Reports as InValid')
							->modalDescription('Are you sure you want to mark the selected reports as "Reviewed - InValid"? This indicates the report was Illegitimate. No Further actions needed.')
							->action(function (Collection $records) {
								$records->each(function (Report $report) {
									$report->status = 'reviewed_invalid'; // Set the new status
									$report->reviewed_by_admin_id = Auth::id();
									$report->reviewed_at = now();
									$report->save();
								});
								Notification::make()
									->title($records->count() . ' ' . Str::plural('report', $records->count()) . ' marked as Invalid.')
									->success() // Or ->info()
									->send();
							})
							->deselectRecordsAfterCompletion(),			
										
						

                    BulkAction::make('mark_as_pending') // Example: Revert to pending
                        ->label('Mark Selected as Pending')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            $records->each(function (Report $report) {
                                $report->status = 'pending';
                                $report->reviewed_by_admin_id = null; // Clear reviewer info
                                $report->reviewed_at = null;
                                $report->admin_notes = ($report->admin_notes ? $report->admin_notes . "\n" : '') . "[Reverted to Pending by " . Auth::user()->name . " on " . now()->toDateTimeString() . "]";
                                $report->save();
                            });
                            Notification::make()
                                ->title($records->count() . ' ' . Str::plural('report', $records->count()) . ' marked as pending.')
                                ->warning()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    // Authorization methods (NOT STATIC)
    public function canCreate(): bool { return false; } // Users create reports, admins manage
    public function canEdit(Model $record): bool { return auth()->user()->isAdminOrEditor(); }
    public function canDelete(Model $record): bool { return auth()->user()->isAdminOrEditor(); } // Or just isAdmin for deleting actual reports
    public function canDeleteAny(): bool { return auth()->user()->isAdminOrEditor(); } // Or just isAdmin for bulk deleting reports
}