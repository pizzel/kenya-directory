<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead class="bg-gray-50 border-b border-gray-100">
            <tr>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Business</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Category</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Featured Until</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider text-right">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
            @forelse($businesses as $business)
                <tr class="hover:bg-gray-50/50 transition-colors duration-150">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0">
                                <img src="{{ $business->getImageUrl('thumbnail') }}" 
                                     alt="" 
                                     class="w-full h-full object-cover"
                                     onerror="this.src='{{ asset('images/placeholder-card.jpg') }}'">
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">{{ $business->name }}</div>
                                <div class="text-xs text-gray-500">{{ $business->county->name ?? 'No County' }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700">
                            {{ $business->categories->first()->name ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-700">
                             {{ $business->featured_expires_at ? $business->featured_expires_at->format('M d, Y') : 'Indefinite' }}
                        </div>
                        <div class="text-xs text-gray-500 mt-0.5">
                            {{ $business->featured_expires_at ? $business->featured_expires_at->diffForHumans() : '' }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $isExpired = $business->featured_expires_at && $business->featured_expires_at->isPast();
                        @endphp
                        @if($isExpired)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                Expired
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                <i class="fas fa-star mr-1"></i>
                                Featured
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right space-x-2">
                        <a href="{{ route('admin.featured.edit', $business) }}" class="text-indigo-600 hover:text-indigo-900 transition-colors">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button onclick="removeFeatured({{ $business->id }})" class="text-red-600 hover:text-red-900 transition-colors">
                            <i class="fas fa-times-circle"></i>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-star text-4xl text-gray-200 mb-3"></i>
                            <p class="text-gray-500">No featured businesses found.</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($businesses->hasPages())
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30">
        {{ $businesses->withQueryString()->links('pagination::tailwind') }}
    </div>
@endif
