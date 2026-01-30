<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
// use App\Filament\Resources\UserResource\RelationManagers; // If you have any
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope; // If User model uses SoftDeletes
use Illuminate\Support\Facades\Hash; // For hashing password on create/update
use Illuminate\Validation\Rules\Password; // For password validation rules

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Site Administration'; // Ensure this matches your AdminPanelProvider
    protected static ?int $navigationSort = 1;

    public static function canViewAny(): bool
    {
        return auth()->check() && auth()->user()->isAdmin(); // Only super admin can manage users
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true), // Ensure email is unique, ignoring current user on edit

                // ROLE SELECTION DROPDOWN
                Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Administrator (Super Admin)',
                        'editor' => 'Editor (Moderator)',
                        'business_owner' => 'Business Owner',
                        'user' => 'Standard User',
                    ])
                    ->required()
                    ->helperText('Select the role for this user.'),

                Forms\Components\DateTimePicker::make('email_verified_at')
                    ->label('Email Verified At')
                    ->helperText('Set a date to mark email as verified. Leave blank if not verified.'),

                Forms\Components\TextInput::make('password')
                    ->password()
                    // Required on create, optional on edit (only if changing)
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Hash::make($state) : null) // Hash if new password provided
                    ->dehydrated(fn (?string $state): bool => filled($state)) // Only include in update if filled
                    ->rule(Password::defaults()) // Use Laravel's default password rules
                    ->confirmed() // Adds password_confirmation field and rule automatically on create
                    ->helperText('Leave blank to keep current password when editing.'),

                // This field is automatically added by ->confirmed() on the password field FOR CREATE operations.
                // For EDIT, if you want to explicitly show it only when password is being changed,
                // you might need conditional logic or let Filament handle it.
                // Forms\Components\TextInput::make('password_confirmation')
                //     ->password()
                //     ->label('Confirm New Password')
                //     ->requiredWith('password') // Only required if password field is filled
                //     ->visible(fn (string $operation, Forms\Get $get): bool => $operation === 'create' || filled($get('password'))),

                Forms\Components\Toggle::make('blocked_at')
                    ->label('User Blocked')
                    ->helperText('If toggled on, the user will be considered blocked.')
                    ->onColor('danger')
                    ->offColor('success')
                    ->formatStateUsing(fn ($state): bool => (bool) $state) // Convert timestamp/null to boolean for toggle
                    ->dehydrateStateUsing(fn ($state): ?string => $state ? now() : null), // Set timestamp or null
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('email')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('role')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'admin' => 'danger',
                        'editor' => 'warning',
                        'business_owner' => 'info',
                        'user' => 'success',
                        default => 'gray',
                    })->sortable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-circle'),
                Tables\Columns\IconColumn::make('blocked_at')
                    ->label('Blocked')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed') // Blocked
                    ->falseIcon('heroicon-o-lock-open') // Not blocked
                    ->trueColor('danger')
                    ->falseColor('success'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->options([
                        'admin' => 'Administrator',
                        'editor' => 'Editor',
                        'business_owner' => 'Business Owner',
                        'user' => 'Standard User',
                    ]),
                Tables\Filters\TernaryFilter::make('email_verified_at')
                    ->label('Email Verified')
                    ->nullable(),
                Tables\Filters\TernaryFilter::make('blocked_at')
                    ->label('Blocked Status')
                    ->nullable(),
            ])
            ->actions([
				Tables\Actions\Action::make('toggleBlock')
				->label(fn (User $record): string => $record->blocked_at ? 'Unblock User' : 'Block User')
				->icon(fn (User $record): string => $record->blocked_at ? 'heroicon-o-lock-open' : 'heroicon-o-lock-closed')
				->color(fn (User $record): string => $record->blocked_at ? 'success' : 'danger')
				->requiresConfirmation()
				->action(function (User $record) {
					if ($record->blocked_at) {
						$record->blocked_at = null;
					} else {
						$record->blocked_at = now();
					}
					$record->save();
				})
				// Prevent admin from blocking themselves or another admin (optional)
				->visible(fn (User $record): bool => $record->id !== auth()->id() && !$record->isAdmin()),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make() // Be careful with deleting users
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\BusinessesRelationManager::class, // If you want to see businesses owned by this user
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}