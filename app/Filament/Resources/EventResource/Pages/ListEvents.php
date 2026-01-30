<?php

namespace App\Filament\Resources\EventResource\Pages;

use App\Filament\Resources\EventResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder; // Required for query modification
use App\Models\Event; // Required for badge counts
// No need to import ListRecords\Tab if using the FQCN in the return type hint

class ListEvents extends ListRecords
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(), // If admins/editors can create events directly here
        ];
    }

    /**
     * Define the tabs for the event listing page.
     *
     * @return array<string, \Filament\Resources\Pages\ListRecords\Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => ListRecords\Tab::make('All Events')
                ->badge(EventResource::getEloquentQuery()->count()), // Use the resource's base query

            'upcoming_active' => ListRecords\Tab::make('Upcoming & Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active')->where('end_datetime', '>=', now()))
                ->badge(Event::where('status', 'active')->where('end_datetime', '>=', now())->count())
                ->icon('heroicon-o-play-circle'),

            'pending_approval' => ListRecords\Tab::make('Pending Approval')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending_approval'))
                ->badge(Event::where('status', 'pending_approval')->count())
                ->icon('heroicon-o-clock'),

            'past_events' => ListRecords\Tab::make('Past Events')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('end_datetime', '<', now())->orWhere('status', 'past'))
                ->badge(Event::where('end_datetime', '<', now())->orWhere('status', 'past')->count())
                ->icon('heroicon-o-archive-box'),

            'cancelled' => ListRecords\Tab::make('Cancelled Events')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'cancelled'))
                ->badge(Event::where('status', 'cancelled')->count())
                ->icon('heroicon-o-x-circle'),

            // If your Event model uses SoftDeletes and you want a tab for trashed items
            // 'archived' => ListRecords\Tab::make('Archived')
            //     ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
            //     ->badge(Event::onlyTrashed()->count())
            //     ->icon('heroicon-o-trash')
            //     ->visible(fn(): bool => auth()->user()->isAdminOrEditor()), // Example visibility
        ];
    }
}