<x-business-owner-layout>
    
    <div class="bo-container">

        <!-- PAGE HEADER -->
        <div class="bo-page-header">
            <div>
                <h2>My Businesses</h2>
                <p style="color: #64748b; font-size: 0.95rem; margin-top: 5px;">Manage your listings and monitor performance.</p>
            </div>
            <a href="{{ route('business-owner.businesses.create') }}" class="bo-button-primary">
                <i class="fas fa-plus" style="margin-right: 5px;"></i> Add New Business
            </a>
        </div>

        <!-- MAIN CONTENT -->
        <div class="bo-table-card">
            @if($businesses->isEmpty())
                <!-- EMPTY STATE -->
                <div class="bo-empty-state">
                    <div class="bo-empty-state-icon-wrapper">
                        <i class="fas fa-store-slash" style="font-size: 2rem;"></i>
                    </div>
                    <h3>No businesses yet</h3>
                    <p>Get started by adding your first listing to Discover Kenya today.</p>
                    <a href="{{ route('business-owner.businesses.create') }}" class="bo-button-secondary">Add Listing</a>
                </div>
            @else
                <!-- TABLE -->
                <div class="table-responsive">
                    <table class="bo-table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Image</th>
                                <th>Business Name</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th>Performance</th>
                                <th style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($businesses as $business)
                            <tr>
                                <!-- Image -->
                                <td>
                                    <img src="{{ $business->getImageUrl('thumbnail') }}" 
                                         alt="" class="bo-table-img">
                                </td>

                                <!-- Name & Date -->
                                <td>
                                    <div style="font-weight: 600; color: #0f172a; margin-bottom: 3px;">
                                        {{ $business->name }}
                                    </div>
                                    <div style="font-size: 0.8rem; color: #94a3b8;">
                                        Added {{ $business->created_at->format('M d, Y') }}
                                    </div>
                                </td>

                                <!-- Location -->
                                <td>
                                    <span style="color: #475569;">{{ $business->county->name ?? 'N/A' }}</span>
                                </td>

                                <!-- Status -->
                                <td>
                                    @php
                                        $statusClass = match($business->status) {
                                            'active' => 'badge-active',
                                            'pending_approval' => 'badge-pending',
                                            default => 'badge-delisted'
                                        };
                                        $statusLabel = match($business->status) {
                                            'active' => 'Active',
                                            'pending_approval' => 'Pending',
                                            'delisted' => 'Hidden',
                                            default => 'Closed'
                                        };
                                    @endphp
                                    <span class="badge {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <!-- Views -->
                                <td>
                                    <div style="display: flex; align-items: center; gap: 6px; font-size: 0.85rem; color: #64748b;">
                                        <i class="far fa-eye"></i>
                                        <span>{{ number_format($business->views_count ?? 0) }} Views</span>
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td>
                                    <div class="action-group">
                                        <a href="{{ route('listings.show', $business->slug) }}" target="_blank" class="icon-btn" title="View Public Page">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                        
                                        <a href="{{ route('business-owner.businesses.edit', $business) }}" class="icon-btn edit" title="Edit Listing">
                                            <i class="fas fa-pencil-alt"></i>
                                        </a>

                                        <form action="{{ route('business-owner.businesses.destroy', $business) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this listing?');" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="icon-btn delete" title="Delete">
                                                <i class="far fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                @if($businesses->hasPages())
                    <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: center;">
                        {{-- Use your site's standard pagination here --}}
                        {{ $businesses->onEachSide(1)->links() }}
                    </div>
                @endif
            @endif
        </div>
    
    </div>

</x-business-owner-layout>