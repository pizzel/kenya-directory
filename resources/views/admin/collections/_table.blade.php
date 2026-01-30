<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items</th>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
            <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($collections as $collection)
            <tr>
                  <td class="px-6 py-4 whitespace-nowrap w-24">
                    <img class="h-12 w-20 rounded object-cover bg-gray-100" 
                         src="{{ $collection->getCoverImageUrl() }}" 
                         alt=""
                         onerror="this.src='{{ asset('images/placeholder-card.jpg') }}'">
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $collection->title }}</div>
                    <div class="text-xs text-gray-500">/{{ $collection->slug }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="bg-blue-100 text-blue-800 py-0.5 px-2.5 rounded-full text-xs font-medium">
                        {{ $collection->businesses_count }} Businesses
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                     @if($collection->is_active)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                     @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Hidden</span>
                     @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $collection->display_order }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('admin.collections.edit', $collection) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                    <form action="{{ route('admin.collections.destroy', $collection) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete collection?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-gray-500">No collections found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if($collections->hasPages())
<div class="px-4 py-3 border-t border-gray-200">
    {{ $collections->withQueryString()->links('pagination::tailwind') }}
</div>
@endif
