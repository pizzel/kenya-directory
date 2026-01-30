<x-admin.layouts.app>
    <x-slot name="header">
        Create Blog Post
    </x-slot>

    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-sm border border-gray-200">
        <form action="{{ route('admin.posts.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Title -->
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700">Post Title</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

             <!-- Excerpt -->
             <div>
                <label for="excerpt" class="block text-sm font-medium text-gray-700">Excerpt (Summary)</label>
                <textarea name="excerpt" id="excerpt" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('excerpt') }}</textarea>
                @error('excerpt') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <!-- Content -->
            <div>
                <label for="body" class="block text-sm font-medium text-gray-700">Content</label>
                <div class="mt-1">
                     <textarea name="content[0][data][text]" id="body" rows="10" 
                        class="block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">{{ old('content.0.data.text') }}</textarea>
                     <input type="hidden" name="content[0][type]" value="text_block">
                     <p class="mt-2 text-xs text-gray-500">Supports HTML. Future update will include a rich text editor.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Status -->
                <div>
                     <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status" required 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                        <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending_review" {{ old('status') == 'pending_review' ? 'selected' : '' }}>Pending Review</option>
                        <option value="published" {{ old('status') == 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>

                <!-- Published At -->
                <div>
                    <label for="published_at" class="block text-sm font-medium text-gray-700">Publish Date</label>
                    <input type="datetime-local" name="published_at" id="published_at" value="{{ old('published_at') }}" 
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
                <a href="{{ route('admin.posts.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">Create Post</button>
            </div>
        </form>
    </div>
</x-admin.layouts.app>
