<?php

namespace App\Filament\Resources\BusinessResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\HeroSliderHistory; // Your model for hero placements
use App\Models\Business; // For type hinting on ownerRecord if needed, not strictly necessary
use App\Rules\MaxConcurrentHeroPlacements; // Your custom validation rule
use Filament\Forms\Components\Actions\Action as FormAction;
//use Illuminate\Support\Facades\Log; // For debugging if necessary

class HeroSliderHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'heroSliderHistories'; // Matches method name in Business model

    protected static ?string $recordTitleAttribute = 'id'; // Or use getRecordTitle for a more descriptive one

    protected static ?string $modelLabel = 'Hero Feature Period';
    protected static ?string $pluralModelLabel = 'Hero Feature Periods';
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days'; // Changed for better context

    // More descriptive record title for notifications, breadcrumbs etc.
    public function getRecordTitle(?Model $record): ?string
    {
        if (!$record || !isset($record->activated_at)) {
            return $record ? ('Placement #' . $record->id) : 'New Hero Feature Period';
        }
        $appTimezone = config('app.timezone');
        $activated = ($record->activated_at instanceof Carbon) ?
                     $record->activated_at->setTimezone($appTimezone) :
                     Carbon::parse($record->activated_at)->setTimezone($appTimezone);
        return 'Placement starting ' . $activated->format('M j, Y H:i');
    }

    // Helper function for calculating expiry, must be static as it's called with static::
    protected static function updateCalculatedExpiresAt(Set $set, Get $get): void
    {
        $activatedAtStr = $get('activated_at');
        $durationValue = (int) $get('duration_value'); // Cast to int
        $durationUnit = $get('duration_unit');
        $appTimezone = config('app.timezone');

        if ($activatedAtStr && $durationValue > 0 && $durationUnit) {
            try {
                $startDate = Carbon::parse($activatedAtStr, $appTimezone);
                $calculatedExpiry = $startDate->copy();

                if ($durationUnit === 'hours') {
                    if ($durationValue > (24 * 30)) { // Max 30 days in hours
                        $set('calculated_set_to_expire_at', null);
                        // Optionally add a validation error message here if possible,
                        // though field-specific rules are better for direct feedback.
                        return;
                    }
                    $calculatedExpiry->addHours($durationValue);
                } elseif ($durationUnit === 'days') {
                    if ($durationValue > 30) { // Max 30 days
                        $set('calculated_set_to_expire_at', null);
                        return;
                    }
                    $calculatedExpiry->addDays($durationValue);
                } else {
                    $set('calculated_set_to_expire_at', null);
                    return;
                }
                $set('calculated_set_to_expire_at', $calculatedExpiry->toDateTimeString());
            } catch (\Exception $e) {
                //Log::error("Error in updateCalculatedExpiresAt: " . $e->getMessage(), ['data' => $get()]);
                $set('calculated_set_to_expire_at', null);
            }
        } else {
            $set('calculated_set_to_expire_at', null);
        }
    }
	
	public function form(Form $form): Form // NOT STATIC
{
    // Get the ID of the record being edited, or null if creating
    // This is necessary to pass to the MaxConcurrentHeroPlacements rule
    // to exclude the current record from overlap checks when editing.

		$editingRecordModel = null; // Initialize
        $editingRecordId = null;    // Initialize

        // Try to get the model instance associated with the current "Edit" action
        $mountedRecord = $this->getMountedActionFormModel(); // Tries to get the model for the form action
        if ($mountedRecord instanceof Model && $mountedRecord->exists) {
            $editingRecordModel = $mountedRecord;
            $editingRecordId = $mountedRecord->id;
        } else {
            // Fallback if getMountedActionFormModel didn't return a persisted model
            // This is less common for an Edit action but good to have a check
            $recordKey = $this->mountedTableActionRecord; // This might hold the key (ID) of the record
            if ($recordKey && !$mountedRecord) { // If we have a key but no model from getMountedActionFormModel
                // Try to fetch the record using the key if it's not already an object
                if (is_numeric($recordKey) || is_string($recordKey)) {
                     $relatedModelInstance = $this->getRelationship()->getRelated()->newQuery()->find($recordKey);
                     if ($relatedModelInstance instanceof Model && $relatedModelInstance->exists) {
                         $editingRecordModel = $relatedModelInstance;
                         $editingRecordId = $relatedModelInstance->id;
                     }
                }
            }
        }
        // If still null, means we are likely in a "Create" context, so $editingRecordId remains null.
		$businessId = $this->ownerRecord->id; // Parent Business ID


    return $form
        ->schema([
            Forms\Components\DateTimePicker::make('activated_at')
                ->label('Feature Start Date & Time')
                ->timezone(config('app.timezone'))
                ->default(now()->setTimezone(config('app.timezone'))->addMinute()->startOfMinute()) // Default to now + 1 min
                ->minDate(fn (?Model $record) => // Prevent selecting past for new, allow current for edit
                    $record && $record->exists && $record->activated_at ?
                    (($record->activated_at instanceof Carbon) ? $record->activated_at : Carbon::parse($record->activated_at, config('app.timezone'))) :
                    now()->setTimezone(config('app.timezone'))->addMinute()->startOfMinute()
                )
                ->seconds(false)
                ->native(false)
                ->required()
                ->live(debounce: 500)
                ->afterStateUpdated(fn (Set $set, Get $get) => static::updateCalculatedExpiresAt($set, $get))
                ->rules([ // Attach overlap validation rule here
                    new MaxConcurrentHeroPlacements(
                        $businessId,
                        $editingRecordId
                    ),
                ])
				   ->suffixAction(
                        FormAction::make('set_to_current_time')
                            ->label('Now')
                            ->icon('heroicon-o-clock')
                            ->tooltip('Set to current date and time')
                            ->action(function (Set $set, Get $get) {
                                $currentTime = now(); // Already in app.timezone
                                $set('activated_at', $currentTime->toDateTimeString());
                                static::updateCalculatedExpiresAt($set, $get, $currentTime->toDateTimeString(), $get('duration_value'), $get('duration_unit'));
                            })
                    ),

            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('duration_value')
                    ->label('Duration Value')
                    ->numeric()->minValue(1)->required()->default(1)->live(debounce: 500)
                    ->afterStateUpdated(fn (Set $set, Get $get) => static::updateCalculatedExpiresAt($set, $get))
                    ->rules([
                        fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                            $unit = $get('duration_unit');
                            if ($unit === 'hours' && (int)$value > (24 * 30)) { $fail('For hours, max duration is 720 (approx. 30 days).'); }
                            if ($unit === 'days' && (int)$value > 30) { $fail('For days, max duration is 30.'); }
                        },
                    ]),
                Forms\Components\Select::make('duration_unit')
                    ->label('Duration Unit')
                    ->options(['hours' => 'Hours', 'days' => 'Days'])
                    ->default('days')->required()->live(debounce: 500)
                    ->afterStateUpdated(fn (Set $set, Get $get) => static::updateCalculatedExpiresAt($set, $get)),
            ]),

            Forms\Components\DateTimePicker::make('calculated_set_to_expire_at')
                ->label('Calculated Expiry (' . config('app.timezone') . ')')
                ->timezone(config('app.timezone'))
                ->seconds(false)->native(false)->disabled()->dehydrated(false)
                ->helperText('This is automatically calculated. Actual expiry is saved upon submission.'),

            // REMOVED: Forms\Components\Hidden::make('overlap_validation_trigger')

            Forms\Components\TextInput::make('package_name')->label('Package Name (Optional)')->maxLength(255),
            Forms\Components\TextInput::make('amount_paid')->numeric()->prefix('Ksh')->label('Amount Paid (Optional)')->minValue(0)->nullable(),
            Forms\Components\TextInput::make('payment_reference')->label('Payment Reference (Optional)')->maxLength(255),
            Forms\Components\Textarea::make('notes')->label('Admin Notes (Optional)')->columnSpanFull()->rows(3),
        ])->columns(1);
}

    public function table(Table $table): Table // NOT STATIC
    {
         return $table
            ->columns([
                Tables\Columns\TextColumn::make('activated_at')
                    ->dateTime('M j, Y H:i A')
                    ->timezone(config('app.timezone'))
                    ->sortable()->label('Starts'),

                Tables\Columns\TextColumn::make('set_to_expire_at')
                    ->dateTime('M j, Y H:i A')
                    ->timezone(config('app.timezone'))
                    ->sortable()->label('Expires')
                    ->color(function (HeroSliderHistory $record) {
                        if ($record->set_to_expire_at && $record->set_to_expire_at instanceof Carbon) {
                            if ($record->set_to_expire_at->isPast()) return 'danger';
                        }
                        if ($record->activated_at && $record->activated_at instanceof Carbon) {
                            if ($record->activated_at->isFuture()) return 'warning';
                            if ($record->set_to_expire_at && $record->set_to_expire_at instanceof Carbon &&
                                $record->activated_at->lte(now()->setTimezone(config('app.timezone'))) &&
                                $record->set_to_expire_at->gte(now()->setTimezone(config('app.timezone')))) return 'success';
                        }
                        return 'gray';
                    })
                    ->description(function(HeroSliderHistory $record) {
                        $appTimezone = config('app.timezone');
                        $nowInAppTz = now()->setTimezone($appTimezone);

                        // Ensure dates are Carbon instances in the app's timezone for comparison and diffing
                        $expiresAt = ($record->set_to_expire_at instanceof Carbon) ?
                                     $record->set_to_expire_at->setTimezone($appTimezone) :
                                     ($record->set_to_expire_at ? Carbon::parse($record->set_to_expire_at, $appTimezone) : null);

                        $activatedAt = ($record->activated_at instanceof Carbon) ?
                                       $record->activated_at->setTimezone($appTimezone) :
                                       ($record->activated_at ? Carbon::parse($record->activated_at, $appTimezone) : null);

                        if ($expiresAt && $expiresAt->isPast()) {
                            // diffForHumans(null, true) removes "ago" / "from now"
                            return 'Expired (' . $expiresAt->diffForHumans($nowInAppTz, true) . ' ago)';
                        }
                        if ($activatedAt && $activatedAt->isFuture()) {
                            return 'Scheduled (in ' . $activatedAt->diffForHumans($nowInAppTz, true) . ')';
                        }
                        if ($activatedAt && $expiresAt &&
                            $activatedAt->lte($nowInAppTz) && $expiresAt->gte($nowInAppTz)) {
                            return 'Active (Expires in ' . $expiresAt->diffForHumans($nowInAppTz, true) . ')';
                        }
                        return 'Status Undetermined';
                    }),

                Tables\Columns\TextColumn::make('package_name')->searchable()->placeholder('N/A')->toggleable(),
                Tables\Columns\TextColumn::make('amount_paid')->money('Ksh')->sortable()->placeholder('N/A')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('admin.name')->label('Set By')->searchable()->placeholder('N/A')->toggleable(),
                Tables\Columns\TextColumn::make('notes')->limit(30)->tooltip(fn (HeroSliderHistory $record) => $record->notes)->placeholder('N/A')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')->label('Record Date')->dateTime('M j, Y')->timezone(config('app.timezone'))->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('active_now')->label('Currently Active')
                    ->query(fn (Builder $query): Builder => $query->where('activated_at', '<=', now())->where('set_to_expire_at', '>=', now())),
                Tables\Filters\Filter::make('expired')->label('Expired')
                    ->query(fn (Builder $query): Builder => $query->where('set_to_expire_at', '<', now())),
                Tables\Filters\Filter::make('future')->label('Scheduled (Future)')
                    ->query(fn (Builder $query): Builder => $query->where('activated_at', '>', now())),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Add New Hero Placement')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['admin_id'] = Auth::id();
                        $appTimezone = config('app.timezone');

                        $activatedAtInputString = $data['activated_at'] ?? null;
                        $durationValue = isset($data['duration_value']) ? (int)$data['duration_value'] : null;
                        $durationUnit = $data['duration_unit'] ?? null;

                        if ($activatedAtInputString && $durationValue !== null && $durationValue > 0 && $durationUnit) {
                            try {
                                $startDateInAppTz = Carbon::parse($activatedAtInputString, $appTimezone);
                                $expiresAtInAppTz = $startDateInAppTz->copy();

                                if ($durationUnit === 'hours') $expiresAtInAppTz->addHours($durationValue);
                                elseif ($durationUnit === 'days') $expiresAtInAppTz->addDays($durationValue);
                                else $expiresAtInAppTz = null;

                                $data['activated_at'] = Carbon::parse($activatedAtInputString, $appTimezone)->toDateTimeString();
                                $data['set_to_expire_at'] = $expiresAtInAppTz ? $expiresAtInAppTz->toDateTimeString() : null;
                            } catch (\Exception $e) {
                                //Log::error('CreateAction RM: Exception during date mutation for set_to_expire_at: ' . $e->getMessage(), ['data' => $data]);
                                unset($data['activated_at'], $data['set_to_expire_at']);
                            }
                        } else {
                             //Log::warning('CreateAction RM: Missing date/duration fields for expiry calculation.', ['data' => $data]);
                             unset($data['activated_at'], $data['set_to_expire_at']);
                        }
                        unset($data['duration_value'], $data['duration_unit'], $data['calculated_set_to_expire_at'], $data['overlap_validation_trigger']);
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->mutateFormDataUsing(function (array $data, Model $record): array {
                        $appTimezone = config('app.timezone');
                        $activatedAtInputString = $data['activated_at'] ?? null;
                        $durationValue = isset($data['duration_value']) ? (int)$data['duration_value'] : null;
                        $durationUnit = $data['duration_unit'] ?? null;

                        if (isset($data['activated_at'])) {
                            try {
                                $data['activated_at'] = Carbon::parse($data['activated_at'], $appTimezone)->toDateTimeString();
                            } catch (\Exception $e) {
                                //Log::error('EditAction RM: Exception parsing activated_at: ' . $e->getMessage());
                                $data['activated_at'] = ($record->activated_at instanceof Carbon) ? $record->activated_at->toDateTimeString() : $record->getOriginal('activated_at');
                            }
                        }

                        if (isset($data['activated_at']) && $durationValue !== null && $durationValue > 0 && $durationUnit) {
                            try {
                                $startDateInAppTz = Carbon::parse($data['activated_at'], $appTimezone);
                                $expiresAtInAppTz = $startDateInAppTz->copy();
                                if ($durationUnit === 'hours') $expiresAtInAppTz->addHours($durationValue);
                                elseif ($durationUnit === 'days') $expiresAtInAppTz->addDays($durationValue);
                                else $expiresAtInAppTz = null;
                                $data['set_to_expire_at'] = $expiresAtInAppTz ? $expiresAtInAppTz->toDateTimeString() : (($record->set_to_expire_at instanceof Carbon) ? $record->set_to_expire_at->toDateTimeString() : $record->getOriginal('set_to_expire_at'));
                            } catch (\Exception $e) {
                                //Log::error('EditAction RM: Exception during date calculation: ' . $e->getMessage());
                                $data['set_to_expire_at'] = ($record->set_to_expire_at instanceof Carbon) ? $record->set_to_expire_at->toDateTimeString() : $record->getOriginal('set_to_expire_at');
                            }
                        } elseif (!isset($data['duration_value']) && !isset($data['duration_unit'])) {
                            $data['set_to_expire_at'] = ($record->set_to_expire_at instanceof Carbon) ? $record->set_to_expire_at->toDateTimeString() : $record->getOriginal('set_to_expire_at');
                        }
                        unset($data['duration_value'], $data['duration_unit'], $data['calculated_set_to_expire_at'], $data['overlap_validation_trigger']);
                        return $data;
                    })
					->disabled(function (Model $record, RelationManager $livewire): bool {
								if (auth()->user()->isEditor()) {
									$expiresAt = ($record->set_to_expire_at instanceof Carbon)
										? $record->set_to_expire_at
										: Carbon::parse($record->set_to_expire_at);
									return $expiresAt->isPast();
								}
								return false; // Admins are not disabled by this rule
							})
							->tooltip(function (Model $record, RelationManager $livewire): ?string {
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
                Tables\Actions\ViewAction::make(),
                Tables\Actions\DeleteAction::make(), // This will use SoftDeletes if enabled on HeroSliderHistory model
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(), // Uses SoftDeletes
                ]),
            ])
            ->defaultSort('activated_at', 'desc');
    }

    public function canCreate(): bool { return auth()->user()->isAdminOrEditor(); }
    public function canEdit(Model $record): bool { return auth()->user()->isAdminOrEditor(); }
    public function canDelete(Model $record): bool { return auth()->user()->isAdmin(); }
    public function canDeleteAny(): bool { return auth()->user()->isAdmin(); }
}