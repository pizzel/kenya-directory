@props(['event'])

<div class="listing-card group" style="background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: transform 0.2s, box-shadow 0.2s; border: 1px solid #f3f4f6;">
    
    <a href="{{ route('events.show.public', $event->slug) }}" class="block h-full">
        {{-- Image Container --}}
        <div class="relative overflow-hidden aspect-[4/3]">
             {{-- Status Badge Logic --}}
             @php
                $statusColor = 'bg-gray-800 text-white';
                $statusText = 'Upcoming';
                if($event->start_datetime->isToday()) {
                    $statusColor = 'bg-green-500 text-white';
                    $statusText = 'Today';
                } elseif($event->start_datetime->isPast()) {
                    $statusColor = 'bg-gray-200 text-gray-600';
                    $statusText = 'Past';
                }
             @endphp

            <span style="position: absolute; top: 12px; left: 12px; font-size: 0.65rem; font-weight: 800; padding: 5px 10px; border-radius: 20px; z-index: 10; letter-spacing: 0.5px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);" class="{{ $statusColor }}">
                {{ $statusText }}
            </span>
            
            <img src="{{ $event->getFirstMediaUrl('images', 'card') ?: asset('images/placeholder-event.jpg') }}" 
                 alt="{{ $event->title }}" 
                 style="width: 100%; height: 200px; object-fit: cover; transition: transform 0.5s;"
                 onmouseover="this.style.transform='scale(1.05)'"
                 onmouseout="this.style.transform='scale(1)'">
            
            {{-- Date Overlay (Calendar Style) --}}
            <div style="position: absolute; bottom: 12px; right: 12px; background: white; padding: 6px 12px; border-radius: 8px; text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.1); line-height: 1.1;">
                <div style="font-size: 0.7rem; color: #ef4444; font-weight: 700; text-transform: uppercase;">{{ $event->start_datetime->format('M') }}</div>
                <div style="font-size: 1.1rem; color: #1f2937; font-weight: 800;">{{ $event->start_datetime->format('d') }}</div>
            </div>
        </div>

        <div class="p-4" style="padding: 15px;">
            {{-- Title --}}
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #1a202c; margin-bottom: 6px; line-height: 1.4; min-height: 3em; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {{ Str::limit($event->title, 50) }}
            </h3>
            
            {{-- Location --}}
            <p style="font-size: 0.85rem; color: #64748b; margin-bottom: 12px; display: flex; align-items: center;">
                <i class="fas fa-map-marker-alt" style="color: #cbd5e1; margin-right: 6px;"></i> 
                {{ $event->county ? $event->county->name : 'Online / Kenya' }}
            </p>

            {{-- Footer: Price & Time --}}
            <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 12px; border-top: 1px solid #edf2f7;">
                <div style="font-size: 0.75rem; color: #64748b; display: flex; align-items: center;">
                    <i class="far fa-clock" style="margin-right: 5px;"></i> {{ $event->start_datetime->format('H:i') }}
                </div>

                <div style="font-weight: 700; font-size: 0.9rem; color: #2563eb;">
                    {{ $event->is_free ? 'Free' : ($event->price ? 'Ksh ' . number_format($event->price) : 'TBA') }}
                </div>
            </div>
        </div>
    </a>
</div>