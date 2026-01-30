<?php

namespace App\Filament\Resources\EventResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model; // <<< THIS IS THE CRUCIAL IMPORT
use App\Models\Report;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Illuminate\Support\Collection;
use Filament\Notifications\Notification;

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports'; // Relationship name on Event model
    protected static ?string $recordTitleAttribute = 'report_reason';

    protected static ?string $pluralModelLabel = 'User Reports for this Event';
    protected static ?string $modelLabel = 'User Report';

    public function form(Form $form): Form
    {
        // Identical form to the one in BusinessResource's ReportsRelationManager
        return $form
            ->schema([
                Forms\Components\Section::make('Report Information')->schema([
                    Forms\Components\Select::make('user_id')->relationship('user', 'name')->disabled()->label('Reported By'),
                    Forms\Components\TextInput::make('ip_address')->disabled()->label('Reporter IP (if guest)'),
                    Forms\Components\TextInput::make('report_reason')->formatStateUsing(fn ($state) => $state ? Str::title(str_replace('_', ' ', $state)) : 'N/A')->disabled()->label('Reason'),
                    Forms\Components\Textarea::make('details')->label('User Provided Details')->columnSpanFull()->disabled()->rows(3),
                ])->columns(2),
                Forms\Components\Section::make('Moderation Action')->schema([
                    Forms\Components\Select::make('status')->options(['pending' => 'Pending', /*...*/ 'resolved' => 'Resolved'])->required(),
                    Forms\Components\Textarea::make('admin_notes')->label('Moderator Notes')->columnSpanFull(),
                ])->columns(1),
            ]);
    }

 public function table(Table $table): Table
    {
        // Identical table structure to the one in BusinessResource's ReportsRelationManager
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_reason')->label('Reason')->formatStateUsing(fn ($s): string => Str::title(str_replace('_',' ',$s)))->searchable()->sortable()->description(fn (Report $r): string => Str::limit($r->details, 40)),
                Tables\Columns\TextColumn::make('user.name')->label('Reporter')->searchable()->placeholder('Guest'),
                Tables\Columns\TextColumn::make('status')->badge()->colors([/* colors */])->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Reported On')->dateTime()->sortable(),
                // ... other relevant columns like reviewed_by, reviewed_at
            ])
            ->filters([ SelectFilter::make('status')->options([/* status options */])->label('Report Status') ])
            ->headerActions([ /* No CreateAction */ ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Review/Process')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['reviewed_by_admin_id'] = Auth::id();
                        $data['reviewed_at'] = now();
                        return $data;
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('mark_as_resolved') /* ... as before ... */,
                    BulkAction::make('mark_as_valid')   /* ... as before ... */,
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
    public function canCreate(): bool { return false; }
    public function canEdit(Model $record): bool { return auth()->user()->isAdminOrEditor(); }
    public function canDelete(Model $record): bool { return auth()->user()->isAdminOrEditor(); }
    public function canDeleteAny(): bool { return auth()->user()->isAdminOrEditor(); }
}

