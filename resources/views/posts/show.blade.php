@extends('layouts.site')

@section('title', $post->title)
@section('meta_description', $post->meta_description ?? $post->excerpt ?? Str::limit(strip_tags(json_encode($post->content)), 155))

@php
    // Calculate Read Time
    $wordCount = str_word_count(strip_tags(json_encode($post->content)));
    $readTime = max(1, ceil($wordCount / 200));
@endphp

@section('breadcrumbs')
    <a href="{{ route('home') }}">Home</a> /
    <a href="{{ route('posts.index') }}">Magazine</a> /
    <span>{{ Str::limit($post->title, 40) }}</span>
@endsection

@section('content')
    <div class="single-post-container container max-w-5xl mx-auto">
        
        {{-- HERO HEADER --}}
        <header class="post-header text-center mb-10 pb-8 border-b border-gray-100" style="margin-bottom: 40px; padding-bottom: 30px; border-bottom: 1px solid #f1f5f9; text-align: center;">
            <div class="post-meta mb-4 text-sm font-semibold text-blue-600 uppercase tracking-wider">
                {{ $post->category->name ?? 'Travel Guide' }}
            </div>
            
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 mb-6 leading-tight" style="font-size: 2.5rem; color: #1e293b; font-weight: 800; line-height: 1.2; margin-bottom: 20px;">
                {{ $post->title }}
            </h1>

            <div class="post-meta flex justify-center items-center gap-6 text-gray-500 text-sm" style="display: flex; justify-content: center; align-items: center; gap: 20px; color: #64748b;">
                <span class="flex items-center"><i class="far fa-calendar mr-2"></i> {{ $post->published_at->format('M j, Y') }}</span>
                <span class="flex items-center"><i class="far fa-clock mr-2"></i> {{ $readTime }} min read</span>
                <span class="flex items-center"><i class="far fa-eye mr-2"></i> {{ number_format($post->views) }} views</span>
                
                {{-- Like Button --}}
                @php $isLiked = auth()->check() ? auth()->user()->likedPosts->contains($post) : false; @endphp
                <button id="likeButton" class="like-btn {{ $isLiked ? 'text-red-500' : 'text-gray-400 hover:text-red-500' }} transition-colors" 
                        data-post-id="{{ $post->id }}" data-like-url="{{ route('posts.like', $post) }}" style="background: none; border: none; cursor: pointer; font-size: 1rem;">
                    <i class="{{ $isLiked ? 'fas' : 'far' }} fa-heart"></i> <span id="likeCount">{{ $post->likers()->count() }}</span>
                </button>
            </div>
        </header>

        <div class="post-layout-grid grid lg:grid-cols-12 gap-12" style="display: grid; grid-template-columns: 1fr 300px; gap: 50px;">
            
            {{-- MAIN ARTICLE COLUMN --}}
            <main class="post-main-content lg:col-span-8" style="font-size: 1.125rem; line-height: 1.8; color: #334155;">
                
                <div class="post-content">
                    @php $content = is_array($post->content) ? $post->content : []; @endphp
                    @for ($i = 0; $i < count($content); $i++)
                        @php $block = $content[$i]; @endphp

                        {{-- BUSINESS BLOCK (The "Editors Choice" Card) --}}
                        @if ($block['type'] === 'business_block' && !empty($block['data']['business_id']))
                            @php
                                $featuredBusiness = \Cache::remember('post_business_' . $block['data']['business_id'], 60, fn() => \App\Models\Business::with('county')->find($block['data']['business_id']));
                                
                                // Peek ahead for description
                                $textContent = null;
                                if (isset($content[$i + 1]) && $content[$i + 1]['type'] === 'text_block') {
                                    $textContent = $content[$i + 1]['data']['text'];
                                    $i++; 
                                }
                            @endphp

                            @if ($featuredBusiness)
                                <section class="post-feature-wrapper my-8 bg-white border border-gray-100 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-shadow" style="margin: 40px 0; background: white; border: 1px solid #f1f5f9; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                                    
                                    {{-- Image --}}
                                    <figure class="post-feature-image h-64 overflow-hidden relative">
                                        <a href="{{ route('listings.show', $featuredBusiness->slug) }}">
                                            <img src="{{ $featuredBusiness->getImageUrl('card') }}" alt="{{ $featuredBusiness->name }}" style="width: 100%; height: 250px; object-fit: cover;">
                                            <span style="position: absolute; top: 15px; left: 15px; background: white; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; color: #1e293b; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                                <i class="fas fa-star text-yellow-400"></i> Editors Pick
                                            </span>
                                        </a>
                                    </figure>

                                    <div class="p-6" style="padding: 25px;">
                                        <div class="flex justify-between items-start mb-4" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                                            <div>
                                                <h2 style="font-size: 1.5rem; font-weight: 800; color: #1e293b; margin: 0;">
                                                    <a href="{{ route('listings.show', $featuredBusiness->slug) }}" style="text-decoration: none; color: inherit;">{{ $featuredBusiness->name }}</a>
                                                </h2>
                                                <p style="color: #64748b; font-size: 0.95rem; margin-top: 5px;">
                                                    <i class="fas fa-map-marker-alt text-blue-500 mr-1"></i> {{ $featuredBusiness->address }}
                                                </p>
                                            </div>
                                            <a href="{{ route('listings.show', $featuredBusiness->slug) }}" style="background: #eff6ff; color: #2563eb; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; text-decoration: none;">
                                                View <span class="hidden sm:inline">Details</span> &rarr;
                                            </a>
                                        </div>

                                        @if ($textContent)
                                            <div class="post-feature-description text-gray-600 text-base" style="color: #475569; font-size: 1rem; line-height: 1.6;">
                                                {!! $textContent !!}
                                            </div>
                                        @endif
                                    </div>
                                </section>
                            @endif

                        {{-- TEXT BLOCK (The Story) --}}
                        @elseif ($block['type'] === 'text_block' && !empty($block['data']['text']))
                            <div class="text-content-block mb-6" style="margin-bottom: 25px;">
                                {!! $block['data']['text'] !!}
                            </div>
                        @endif
                    @endfor
                </div>
                
                {{-- Author Bio / Footer --}}
                <div class="mt-12 pt-8 border-t border-gray-100 flex items-center" style="margin-top: 50px; padding-top: 30px; border-top: 1px solid #f1f5f9;">
                    <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-xl text-gray-500 font-bold mr-4" style="width: 50px; height: 50px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; margin-right: 15px;">
                        {{ substr($post->author->name ?? 'A', 0, 1) }}
                    </div>
                    <div>
                        <div class="text-xs uppercase text-gray-400 font-bold">Written by</div>
                        <div class="font-bold text-gray-800">{{ $post->author->name ?? 'Discover Kenya Team' }}</div>
                    </div>
                </div>

            </main>
            
            {{-- SIDEBAR: RECENT & RELATED --}}
            <aside class="post-sidebar lg:col-span-4">
                <div class="sticky top-24" style="position: sticky; top: 100px;">
                    
                    {{-- Widget: More Stories --}}
                    <div class="sidebar-widget bg-white p-6 rounded-2xl border border-gray-100 shadow-sm" style="background: white; padding: 25px; border-radius: 16px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
                        <h4 style="font-size: 0.8rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.05em;">More Stories</h4>
                        
                        @if($recentPosts->isNotEmpty())
                            <ul class="space-y-4" style="list-style: none; padding: 0; margin: 0;">
                                @foreach($recentPosts as $recentPost)
                                    <li style="margin-bottom: 15px; border-bottom: 1px solid #f8fafc; padding-bottom: 15px;">
                                        <a href="{{ route('posts.show', $recentPost->slug) }}" class="group block">
                                            <h5 style="font-size: 1rem; font-weight: 600; color: #1e293b; line-height: 1.4; margin-bottom: 5px; transition: color 0.2s;" onmouseover="this.style.color='#2563eb'" onmouseout="this.style.color='#1e293b'">
                                                {{ $recentPost->title }}
                                            </h5>
                                            <span style="font-size: 0.8rem; color: #94a3b8;">{{ $recentPost->published_at->format('M j') }}</span>
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-gray-400 text-sm">No other posts yet.</p>
                        @endif
                    </div>

                    {{-- Widget: Explore (CTA) --}}
                    <div class="mt-8 bg-blue-50 p-6 rounded-2xl text-center" style="margin-top: 30px; background: #eff6ff; padding: 25px; border-radius: 16px;">
                        <h4 style="color: #1e40af; font-weight: 800; margin-bottom: 10px;">Ready to explore?</h4>
                        <p style="color: #3b82f6; font-size: 0.9rem; margin-bottom: 20px;">Find the best hotels, restaurants, and adventures near you.</p>
                        <a href="{{ route('listings.index') }}" style="display: inline-block; background: #2563eb; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600; width: 100%;">
                            Start Searching
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>
    
    {{-- CSS for Blog Specifics (Add to app.css ideally) --}}
    <style>
        .text-content-block p { margin-bottom: 1.5em; }
        .text-content-block h2 { font-size: 1.8rem; font-weight: 700; color: #1e293b; margin-top: 2em; margin-bottom: 0.75em; }
        .text-content-block h3 { font-size: 1.5rem; font-weight: 600; color: #334155; margin-top: 1.5em; margin-bottom: 0.75em; }
        .text-content-block ul { list-style: disc; margin-left: 1.5em; margin-bottom: 1.5em; }
        .text-content-block img { border-radius: 12px; margin: 2em 0; width: 100%; }
        
        @media (max-width: 1024px) {
            .post-layout-grid { display: block; }
            .post-sidebar { margin-top: 50px; }
        }
    </style>
@endsection