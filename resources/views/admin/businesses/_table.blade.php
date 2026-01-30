<table class="min-w-full divide-y divide-gray-200" id="businesses-table">
    <thead class="bg-gray-50">
        <tr>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Business</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Owner</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Added</th>
            <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($businesses as $business)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-10 w-10">
                            <img class="h-10 w-10 rounded object-cover bg-gray-100" 
                                 src="{{ $business->getImageUrl('thumbnail') }}" 
                                 alt=""
                                 onerror="this.src='{{ asset('images/placeholder-card.jpg') }}'">
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900">{{ $business->name }}</div>
                            <div class="text-xs text-gray-500">{{ $business->category->name ?? 'Uncategorized' }}</div>
                        </div>
                    </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $business->owner->name ?? 'Unknown' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $business->county->name ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $business->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $business->status === 'pending_approval' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $business->status === 'delisted' ? 'bg-red-100 text-red-800' : '' }}">
                        {{ ucwords(str_replace('_', ' ', $business->status)) }}
                    </span>
                    @if($business->is_featured)
                        <span class="ml-1 px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Featured</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $business->created_at->format('M j, Y') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('admin.businesses.edit', $business) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                    <form action="{{ route('admin.businesses.destroy', $business) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this business?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-gray-500">No businesses found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if($businesses->hasPages())
<div class="px-4 py-3 border-t border-gray-200">
    {{ $businesses->withQueryString()->links('pagination::tailwind') }}
</div>
@endif
