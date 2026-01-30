<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use Filament\Forms\Get;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Content Management'; // Group it with other content types

    protected static ?int $navigationSort = 1; // Show it at the top of its group
	

public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Post Details')
                        ->schema([
                            Forms\Components\TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)
                                ->afterStateUpdated(fn (Forms\Set $set, ?string $state) => $set('slug', \Illuminate\Support\Str::slug($state))),
                            Forms\Components\TextInput::make('slug')->required()->maxLength(255)->unique(\App\Models\Post::class, 'slug', ignoreRecord: true),
                            Forms\Components\Textarea::make('excerpt')->helperText('A short summary of the post.')->maxLength(65535),
                        ]),
                    
                    Forms\Components\Section::make('Post Content')
                        ->schema([
                            Forms\Components\Repeater::make('content')
                                ->label('Content Blocks')
                                ->reorderableWithButtons()
                                ->collapsible()
                                ->schema([
                                    Forms\Components\Select::make('type')
                                        ->options([
                                            'text_block' => 'Text Block',
                                            'business_block' => 'Featured Business Block',
                                        ])
                                        ->required()
                                        ->live(),

                                    Forms\Components\RichEditor::make('text')
                                        ->label('Content')
                                        ->statePath('data.text')
                                        ->visible(fn (Get $get) => $get('type') === 'text_block'),

                                    // <<< THIS IS THE FINAL, CORRECTED SELECT FIELD >>>
                                    Forms\Components\Select::make('business_id')
                                        ->label('Select Business to Feature')
                                        ->statePath('data.business_id')
                                        ->searchable()
                                        // Step 1: Provide the initial options. When the form loads, this will
                                        // find the currently selected business and show its name.
                                        ->options(function (Get $get): array {
                                            $businessId = $get('data.business_id');
                                            if (!$businessId) {
                                                return []; // If it's a new block, there are no initial options
                                            }
                                            // Find the specific business and return it as the only option
                                            $business = \App\Models\Business::find($businessId);
                                            return $business ? [$business->id => $business->name] : [];
                                        })
                                        // Step 2: Provide the search results when the user types.
                                        // This will override the initial options list during the search.
                                        ->getSearchResultsUsing(fn (string $search): array =>
                                            \App\Models\Business::where('name', 'like', "%{$search}%")
                                                ->limit(50)
                                                ->pluck('name', 'id')
                                                ->all()
                                        )
                                        ->visible(fn (Get $get) => $get('type') === 'business_block')
                                        ->required(fn (Get $get) => $get('type') === 'business_block'),

                                ])
                                ->addActionLabel('Add New Content Block')
                                ->defaultItems(1),
                        ]),
                ])
                ->columnSpan(['lg' => 2]),

            Forms\Components\Group::make()
                ->schema([
                    Forms\Components\Section::make('Publishing')
                        ->schema([
                            Forms\Components\Select::make('user_id')->label('Author')->relationship('author', 'name')->searchable()->required()->default(auth()->id()),
                            Forms\Components\Select::make('status')->options(['draft' => 'Draft', 'pending_review' => 'Pending Review', 'published' => 'Published'])->required()->default('draft'),
                            Forms\Components\DateTimePicker::make('published_at')->label('Publish Date')->helperText('Set a future date to schedule the post.')->default(now()),
                        ]),
                    Forms\Components\Section::make('Image')
                        ->schema([
                            Forms\Components\FileUpload::make('featured_image_url')
                                ->label('Featured Image')
                                ->image()->disk('public')->directory('post-images')
                                ->helperText('Optional. If left empty, a cover will be auto-assigned from the first featured business.'),
                        ]),
                    Forms\Components\Section::make('SEO Details')
                        ->schema([
                            Forms\Components\TextInput::make('meta_description')->label('Meta Description')->maxLength(255),
                            Forms\Components\TagsInput::make('meta_keywords')->label('Meta Keywords'),
                        ]),
                ])
                ->columnSpan(['lg' => 1]),
        ])
        ->columns(3);
}

   public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\ImageColumn::make('featured_image_url')
                ->label('Image'),

            Tables\Columns\TextColumn::make('title')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('author.name')
                ->sortable(),

            Tables\Columns\TextColumn::make('status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'draft' => 'gray',
                    'pending_review' => 'warning',
                    'published' => 'success',
                })
                ->sortable(),

            Tables\Columns\TextColumn::make('published_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
                
            Tables\Columns\TextColumn::make('created_at')
                ->dateTime()
                ->sortable()
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'draft' => 'Draft',
                    'pending_review' => 'Pending Review',
                    'published' => 'Published',
                ]),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
        ]);
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
