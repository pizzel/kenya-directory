<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event</th>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Organizer</th>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
            <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($events as $event)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">{{ $event->title }}</div>
                    <div class="text-xs text-gray-500">{{ Str::limit(strip_tags($event->description), 50) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $event->business->name ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $event->start_datetime->format('M j, H:i') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                     <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $event->status === 'active' ? 'bg-green-100 text-green-800' : '' }}
                        {{ $event->status === 'pending_approval' ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $event->status === 'cancelled' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $event->status === 'past' ? 'bg-gray-100 text-gray-800' : '' }}">
                        {{ ucwords(str_replace('_', ' ', $event->status)) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('admin.events.edit', $event) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                    <form action="{{ route('admin.events.destroy', $event) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete event?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="px-6 py-10 text-center text-gray-500">No events found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if($events->hasPages())
<div class="px-4 py-3 border-t border-gray-200">
    {{ $events->withQueryString()->links('pagination::tailwind') }}
</div>
@endif
