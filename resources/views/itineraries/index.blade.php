@extends('layouts.site')

@section('title', 'Discover Journey Itineraries - Discover Kenya')

@push('footer-scripts')
<link rel="stylesheet" href="{{ asset('css/itineraries.css') }}">
@endpush

@section('content')
<div class="container py-12">
    {{-- CTA FOR CREATING --}}
    <div class="create-cta">
        <h2 class="text-3xl font-extrabold mb-4">Plan Your Own Adventure</h2>
        <p class="mb-8 opacity-90">Create a master itinerary, invite friends, and track your travel goals for 2026.</p>
        <a href="{{ route('itineraries.create') }}" class="bg-white text-blue-600 px-8 py-3 rounded-full font-bold hover:shadow-lg transition">
            <i class="fas fa-plus mr-2"></i> Start New Itinerary
        </a>
    </div>

    <div class="flex justify-between items-end mb-8">
        <div>
            <h1 class="text-4xl font-black text-gray-900 mb-2">Active Circuits</h1>
            <p class="text-gray-500">Discover hand-picked journeys and community itineraries.</p>
        </div>
    </div>

    @if($itineraries->count() > 0)
        <div class="itinerary-grid">
            @foreach($itineraries as $itinerary)
                <x-itinerary-card :itinerary="$itinerary" />
            @endforeach
        </div>
        
        <div class="mt-12">
            {{ $itineraries->links() }}
        </div>
    @else
        <div class="text-center py-20 bg-slate-50 rounded-3xl border border-dashed border-slate-300">
            <i class="fas fa-map-marked-alt text-5xl text-slate-300 mb-4"></i>
            <h3 class="text-xl font-bold text-slate-600">No itineraries found</h3>
            <p class="text-slate-500">Be the first to create a public journey!</p>
        </div>
    @endif
</div>
@endsection
