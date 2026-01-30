<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Business</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Schedule</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Payment</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($sliders as $slider)
                <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                                <img src="{{ $slider->business->getImageUrl('thumbnail') }}" 
                                     alt="" 
                                     class="w-full h-full object-cover"
                                     onerror="this.src='{{ asset('images/placeholder-card.jpg') }}'">
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $slider->business->name }}</div>
                                <div class="text-xs text-gray-500">{{ $slider->business->county->name ?? 'No County' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-700">
                            <i class="far fa-calendar-check mr-1 text-gray-400"></i> {{ $slider->activated_at->format('M d, Y H:i') }}
                        </div>
                        <div class="text-sm text-gray-500 mt-1">
                            <i class="far fa-calendar-times mr-1 text-gray-400"></i> {{ $slider->set_to_expire_at->format('M d, Y H:i') }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $now = now();
                            $isActive = $slider->activated_at <= $now && $slider->set_to_expire_at > $now;
                            $isScheduled = $slider->activated_at > $now;
                            $isExpired = $slider->set_to_expire_at <= $now;
                        @endphp

                        @if($isActive)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-1 h-1 mr-1.5 rounded-full bg-green-500"></span>
                                Active Now
                            </span>
                        @elseif($isScheduled)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                Scheduled
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Expired
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm font-semibold text-gray-900">KES {{ number_format($slider->amount_paid, 2) }}</div>
                        <div class="text-xs text-gray-500">{{ $slider->package_name ?? 'Standard Package' }}</div>
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.hero-sliders.edit', $slider) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="deleteSlider({{ $slider->id }})" class="text-red-600 hover:text-red-900 transition-colors">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-calendar-alt text-4xl text-gray-200 mb-3"></i>
                            <p class="text-gray-500">No hero sliders found.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($sliders->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
        {{ $sliders->withQueryString()->links('pagination::tailwind') }}
    </div>
@endif
