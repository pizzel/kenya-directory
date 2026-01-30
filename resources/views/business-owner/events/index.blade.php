<x-business-owner-layout>
    
    <div class="bo-container">
        <!-- HEADER -->
        <div class="bo-page-header">
            <div>
                <h2>My Events</h2>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 4px;">Manage your upcoming and past events.</p>
            </div>
            <a href="{{ route('business-owner.events.create') }}" class="bo-button-primary">
                + Add New Event
            </a>
        </div>

        <!-- CONTENT -->
        <div class="bo-table-card">
            @if($events->isEmpty())
                
                <!-- NEW PREMIUM EMPTY STATE -->
                <div class="bo-empty-state">
                    <!-- Icon Wrapper -->
                    <div class="bo-empty-state-icon-wrapper">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                    </div>

                    <h3>No events found</h3>
                    <p>You haven't created any events yet. Get started by hosting your first event and selling tickets online.</p>
                    
                    <!-- Primary Action Button -->
                    <a href="{{ route('business-owner.events.create') }}" class="bo-button-primary">
                        Create Your First Event
                    </a>
                </div>

            @else
                <!-- TABLE (Same as before) -->
                <div class="table-responsive">
                    <table class="bo-table">
                        <thead>
                            <tr>
                                <th>Event Title</th>
                                <th>Host Business</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($events as $event)
                            <tr>
                                <td>
                                    <div style="font-weight: 600; color: #0f172a;">{{ Str::limit($event->title, 40) }}</div>
                                </td>
                                <td>{{ $event->business->name ?? 'N/A' }}</td>
                                <td>
                                    <div style="font-size: 0.9rem;">{{ $event->start_datetime->format('M d, Y') }}</div>
                                    <div style="font-size: 0.8rem; color: #94a3b8;">{{ $event->start_datetime->format('H:i A') }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $event->status === 'published' ? 'badge-active' : 'badge-pending' }}">
                                        {{ ucfirst($event->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <a href="{{ route('events.show.public', $event->slug) }}" target="_blank" class="icon-btn" title="View">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                        <a href="{{ route('business-owner.events.edit', $event) }}" class="icon-btn edit" title="Edit">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                        </a>
                                        <form action="{{ route('business-owner.events.destroy', $event) }}" method="POST" onsubmit="return confirm('Delete this event?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="icon-btn delete" title="Delete">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @if($events->hasPages())
                    <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0;">
                        {{ $events->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-business-owner-layout>