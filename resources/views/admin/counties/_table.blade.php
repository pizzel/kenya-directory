<table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
        <tr>
             <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
            <th scope="col" class="relative px-6 py-3">
                <span class="sr-only">Actions</span>
            </th>
        </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
        @forelse($counties as $county)
            <tr>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ $county->name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <a href="{{ route('admin.counties.edit', $county) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                    <form action="{{ route('admin.counties.destroy', $county) }}" method="POST" class="inline-block" onsubmit="return confirm('Delete county?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="px-6 py-10 text-center text-gray-500">No counties found.</td>
            </tr>
        @endforelse
    </tbody>
</table>

@if($counties->hasPages())
<div class="px-4 py-3 border-t border-gray-200">
    {{ $counties->withQueryString()->links('pagination::tailwind') }}
</div>
@endif
