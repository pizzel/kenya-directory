@extends('layouts.site')

@section('title', 'Explore Curated Collections - ' . config('app.name'))

@section('canonical')
    <link rel="canonical" href="{{ route('collections.index') }}" />
@endsection

@section('breadcrumbs')
    <a href="{{ route('home') }}">Home</a> /
    <span>Collections</span>
@endsection

@section('content')
    <div class="collections-page-container container" style="padding-bottom: 60px;">
        
        {{-- Hero Header --}}
        <div class="page-header text-center mb-10">
            <h1 style="font-size: 2.5rem; font-weight: 800; color: #1e293b; margin-bottom: 10px;">
                Curated Collections
            </h1>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                Expertly hand-picked lists of the best experiences, hidden gems, and getaways across Kenya.
            </p>
        </div>

        @if($collections->isNotEmpty())
            {{-- Responsive Grid --}}
            <div class="collections-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px;">
                @foreach($collections as $collection)
                    <x-discovery-card :collection="$collection" />
                @endforeach
            </div>

            <div class="mt-12 pagination-container">
                {{ $collections->onEachSide(1)->links() }}
            </div>
        @else
            <div class="text-center py-20 bg-gray-50 rounded-xl">
                <i class="far fa-folder-open text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-600">No collections found</h3>
                <p class="text-gray-400 mt-2">Check back soon for new curated lists.</p>
            </div>
        @endif
    </div>
@endsection