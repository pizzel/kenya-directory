<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reported Item</th>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reporter</th>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
            <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($reports as $report)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                    @if($report->business)
                        <div class="text-sm font-medium text-gray-900">{{ $report->business->name }}</div>
                        <div class="text-xs text-blue-500">Business Listing</div>
                    @elseif($report->event)
                        <div class="text-sm font-medium text-gray-900">{{ $report->event->title }}</div>
                        <div class="text-xs text-purple-500">Event</div>
                    @else
                        <span class="text-sm text-gray-500 italic">Item Deleted</span>
                    @endif
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ ucwords(str_replace('_', ' ', $report->report_reason)) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $report->user->name ?? 'Guest/Anonymous' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        {{ $report->status === 'pending' ? 'bg-red-100 text-red-800' : '' }}
                        {{ $report->status === 'resolved' ? 'bg-green-100 text-green-800' : '' }}
                        {{ str_contains($report->status, 'reviewed') ? 'bg-blue-100 text-blue-800' : '' }}">
                        {{ ucwords(str_replace('_', ' ', $report->status)) }}
                    </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ $report->created_at->format('M j, Y H:i') }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('admin.reports.edit', $report) }}" class="text-blue-600 hover:text-blue-900 mr-3">Review</a>
                    <form action="{{ route('admin.reports.destroy', $report) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete this report record?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-gray-400 hover:text-gray-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="6" class="px-6 py-10 text-center text-gray-500">No reports found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if($reports->hasPages())
<div class="px-4 py-3 border-t border-gray-200">
    {{ $reports->withQueryString()->links('pagination::tailwind') }}
</div>
@endif
