<x-admin.layouts.app>
    <x-slot name="header">
        Review Report #{{ $report->id }}
    </x-slot>

    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('admin.reports.update', $report) }}" method="POST" class="p-6 space-y-8">
            @csrf
            @method('PUT')

            <!-- Reported Item Details -->
            <div class="bg-gray-50 p-4 rounded-md border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider mb-2">Reported Item</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500">Item Type</p>
                        <p class="text-sm font-medium text-gray-800">
                            @if($report->business) Business Listing
                            @elseif($report->event) Event
                            @else Unknown
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Item Name</p>
                         <p class="text-sm font-medium text-gray-800">
                            @if($report->business) 
                                <a href="{{ route('admin.businesses.edit', $report->business) }}" class="text-blue-600 hover:underline" target="_blank">{{ $report->business->name }} <i class="fas fa-external-link-alt text-xs"></i></a>
                            @elseif($report->event)
                                <a href="{{ route('admin.events.edit', $report->event) }}" class="text-blue-600 hover:underline" target="_blank">{{ $report->event->title }} <i class="fas fa-external-link-alt text-xs"></i></a>
                            @else
                                <span class="text-red-500 italic">Item no longer exists</span>
                            @endif
                        </p>
                    </div>
                     <div>
                        <p class="text-xs text-gray-500">Reported By</p>
                        <p class="text-sm font-medium text-gray-800">{{ $report->user->name ?? 'Guest/Anonymous' }}</p>
                    </div>
                     <div>
                        <p class="text-xs text-gray-500">Report Date</p>
                        <p class="text-sm font-medium text-gray-800">{{ $report->created_at->format('M j, Y H:i A') }}</p>
                    </div>
                </div>
            </div>

            <!-- Report Details -->
            <div>
                 <h3 class="text-lg font-medium text-gray-900 border-b border-gray-100 pb-2 mb-4">Report Details</h3>
                 <div class="space-y-4">
                     <div>
                        <label class="block text-sm font-medium text-gray-500">Reason</label>
                        <div class="mt-1 p-2 bg-white border border-gray-200 rounded text-gray-800">
                            {{ ucwords(str_replace('_', ' ', $report->report_reason)) }}
                        </div>
                    </div>
                     <div>
                        <label class="block text-sm font-medium text-gray-500">User Provided Details</label>
                        <div class="mt-1 p-3 bg-white border border-gray-200 rounded text-gray-800 min-h-[80px]">
                            {{ $report->details ?? 'No additional details provided.' }}
                        </div>
                    </div>
                 </div>
            </div>

             <!-- Admin Action -->
            <div class="bg-blue-50 p-6 rounded-md border border-blue-100">
                <h3 class="text-lg font-medium text-blue-900 border-b border-blue-200 pb-2 mb-4">Moderation Action</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="status" class="block text-sm font-medium text-blue-900">Status</label>
                        <select name="status" id="status" required 
                            class="mt-1 block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                            <option value="pending" {{ $report->status == 'pending' ? 'selected' : '' }}>Pending Review</option>
                            <option value="reviewed_valid" {{ $report->status == 'reviewed_valid' ? 'selected' : '' }}>Valid Report (Action Needed)</option>
                            <option value="reviewed_invalid" {{ $report->status == 'reviewed_invalid' ? 'selected' : '' }}>Invalid (No Action)</option>
                            <option value="resolved" {{ $report->status == 'resolved' ? 'selected' : '' }}>Resolved</option>
                        </select>
                    </div>

                    <div class="md:col-span-2">
                         <label for="admin_notes" class="block text-sm font-medium text-blue-900">Moderator Notes</label>
                        <textarea name="admin_notes" id="admin_notes" rows="3" placeholder="Explain the action taken..."
                            class="mt-1 block w-full rounded-md border-blue-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ $report->admin_notes }}</textarea>
                    </div>
                </div>
            </div>


            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.reports.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Update Report Status</button>
            </div>
        </form>
    </div>
</x-admin.layouts.app>
