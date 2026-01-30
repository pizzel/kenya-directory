@if($businesses->isEmpty())
    <div class="col-12 text-center py-4">
        <p class="text-muted">No listings found.</p>
    </div>
@else
    @foreach($businesses as $business)
        {{-- The fade-in class makes it look premium when it loads --}}
        <div class="fade-in-up" style="animation-delay: {{ $loop->index * 50 }}ms;">
            <x-business-card :business="$business" />
        </div>
    @endforeach
@endif