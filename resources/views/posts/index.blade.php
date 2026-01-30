@extends('layouts.site')

@section('title', 'Travel Magazine & Guides - ' . config('app.name'))
@section('meta_description', 'Expert guides, hidden gems, and travel stories to help you discover the best of Kenya.')

@section('breadcrumbs')
    <a href="{{ route('home') }}">Home</a> /
    <span>Magazine</span>
@endsection

@section('content')
    <div class="blog-page-container container">
        
        {{-- Magazine Header --}}
        <div class="page-header text-center mb-12">
            <h1 style="font-size: 2.5rem; font-weight: 800; color: #1e293b; letter-spacing: -0.02em;">Travel Magazine</h1>
            <p style="color: #64748b; font-size: 1.1rem; max-width: 600px; margin: 10px auto 0;">
                Curated guides, expert tips, and inspiring stories from across Kenya.
            </p>
        </div>

        @if($posts->isNotEmpty())
            <div class="blog-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 30px;">
                @foreach($posts as $post)
                    <article class="blog-post-card group" style="background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); transition: transform 0.2s; border: 1px solid #f3f4f6;">
                        <a href="{{ route('posts.show', $post->slug) }}" class="block h-full flex flex-col">
                            
                            {{-- Image --}}
                            <div class="card-image-container relative aspect-[16/9] overflow-hidden">
                                <img src="{{ $post->featured_image_url ? Storage::url($post->featured_image_url) : asset('images/placeholder-large.jpg') }}" 
                                     alt="{{ $post->title }}"
                                     style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease;"
                                     onmouseover="this.style.transform='scale(1.05)'"
                                     onmouseout="this.style.transform='scale(1)'">
                                     
                                {{-- Read Time Badge --}}
                                <span style="position: absolute; bottom: 12px; right: 12px; background: rgba(0,0,0,0.7); color: white; font-size: 0.75rem; padding: 4px 8px; border-radius: 6px; font-weight: 600;">
                                    <i class="far fa-clock mr-1"></i> {{ max(1, ceil(str_word_count(strip_tags(json_encode($post->content))) / 200)) }} min read
                                </span>
                            </div>

                            {{-- Content --}}
                            <div class="card-content-area p-6 flex flex-col flex-grow" style="padding: 20px; display: flex; flex-direction: column; height: 100%;">
                                <div class="post-meta mb-3 text-xs font-semibold text-blue-600 uppercase tracking-wider">
                                    {{ $post->category->name ?? 'Guide' }}
                                </div>
                                
                                <h3 class="post-title text-xl font-bold text-gray-900 mb-3 group-hover:text-blue-600 transition-colors" style="font-size: 1.25rem; line-height: 1.4; font-weight: 700; margin-bottom: 10px; color: #1e293b;">
                                    {{ $post->title }}
                                </h3>
                                
                                <p class="post-excerpt text-gray-600 mb-4 flex-grow" style="color: #64748b; font-size: 0.95rem; line-height: 1.6; margin-bottom: 20px;">
                                    {{ $post->excerpt ?? Str::limit(strip_tags($post->content), 120) }}
                                </p>
                                
                                <div class="post-footer flex items-center justify-between pt-4 border-t border-gray-100" style="margin-top: auto; padding-top: 15px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                                    <div class="flex items-center text-sm text-gray-500" style="font-size: 0.85rem; color: #94a3b8;">
                                        <span>{{ $post->published_at->format('M j, Y') }}</span>
                                    </div>
                                    <span style="color: #3b82f6; font-weight: 600; font-size: 0.9rem;">Read Article &rarr;</span>
                                </div>
                            </div>
                        </a>
                    </article>
                @endforeach
            </div>

            <div class="mt-12 pagination-container">
                {{ $posts->onEachSide(1)->links() }}
            </div>
        @else
            <div class="text-center py-20 bg-gray-50 rounded-xl">
                <i class="far fa-newspaper text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-lg font-medium text-gray-600">No articles yet</h3>
                <p class="text-gray-400 mt-2">Our editors are writing new stories. Check back soon!</p>
            </div>
        @endif
    </div>
@endsection