<?php

namespace App\Filament\Resources\BusinessResource\Pages;

use App\Filament\Resources\BusinessResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Business;
use Illuminate\Support\Facades\Cache; // Ensure this is imported
use Illuminate\Support\Facades\DB;    // Ensure this is imported

class ListBusinesses extends ListRecords
{
    protected static string $resource = BusinessResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    /**
     * Define the tabs for the listing page.
     * SUPER OPTIMIZED: Uses caching and direct table counts.
     */
    public function getTabs(): array
    {
        $user = auth()->user();

        // 1. CACHE ALL TAB COUNTS
        // This prevents the database from re-calculating everything on every click.
        $cacheData = Cache::remember('admin_business_tabs_optimized', 120, function () {
            // A. Standard counts using a single lightweight query
            $stats = DB::table('businesses')
                ->selectRaw("
                    COUNT(CASE WHEN deleted_at IS NULL THEN 1 END) as total,
                    COUNT(CASE WHEN status = 'active' AND deleted_at IS NULL THEN 1 END) as active,
                    COUNT(CASE WHEN is_featured = 1 AND (featured_expires_at >= NOW() OR featured_expires_at IS NULL) AND deleted_at IS NULL THEN 1 END) as featured,
                    COUNT(CASE WHEN status = 'pending_approval' AND deleted_at IS NULL THEN 1 END) as pending,
                    COUNT(CASE WHEN status = 'closed_by_reports' AND deleted_at IS NULL THEN 1 END) as closed_reports,
                    COUNT(CASE WHEN status IN ('delisted', 'closed_permanently') AND deleted_at IS NULL THEN 1 END) as delisted,
                    COUNT(CASE WHEN deleted_at IS NOT NULL THEN 1 END) as archived
                ")
                ->first();

            // B. Optimized Reports count: Direct query on the reports table is 10x faster than whereHas
            $pendingReportedBusinesses = DB::table('reports')
                ->where('status', 'pending')
                ->distinct('business_id')
                ->count('business_id');

            return [
                'counts' => (array) $stats,
                'reports' => $pendingReportedBusinesses
            ];
        });

        $tabs = [
            'all' => ListRecords\Tab::make('All Businesses')
                ->modifyQueryUsing(fn (Builder $query) => $query->withoutTrashed())
                ->badge($cacheData['counts']['total']),

            'active' => ListRecords\Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge($cacheData['counts']['active']),
			
			'featured' => ListRecords\Tab::make('Featured')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('is_featured', true)->where(fn($q) => $q->where('featured_expires_at', '>=', now())->orWhereNull('featured_expires_at')))
                ->badge($cacheData['counts']['featured'])
                ->icon('heroicon-o-star'),

            'pending_approval' => ListRecords\Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'pending_approval'))
                ->badge($cacheData['counts']['pending'])
                ->icon('heroicon-o-clock'),

            'with_pending_reports' => ListRecords\Tab::make('Reports')
                ->modifyQueryUsing(fn (Builder $query) =>
                    $query->whereHas('reports', fn ($q) => $q->where('status', 'pending'))
                          ->withCount(['reports as reports_sort' => fn ($q) => $q->where('status', 'pending')])
                          ->orderBy('reports_sort', 'desc')
                )
                ->badge($cacheData['reports'])
                ->icon('heroicon-o-exclamation-triangle'),

            'closed_by_reports' => ListRecords\Tab::make('Closed (Reports)')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'closed_by_reports'))
                ->badge($cacheData['counts']['closed_reports'])
                ->icon('heroicon-o-shield-exclamation'),

            'delisted_or_closed' => ListRecords\Tab::make('Delisted/Closed')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['delisted', 'closed_permanently']))
                ->badge($cacheData['counts']['delisted']),
        ];

        if ($user && ($user->isAdmin() || $user->isEditor())) {
            $tabs['archived'] = ListRecords\Tab::make('Archived')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed())
                ->badge($cacheData['counts']['archived'])
                ->icon('heroicon-o-archive-box-x-mark');
        }

        return $tabs;
    }
}