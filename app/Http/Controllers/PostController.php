<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a paginated list of published blog posts.
     */
    public function index()
    {
        $posts = Post::where('status', 'published')
                     ->where('published_at', '<=', now()) // Only show posts published now or in the past
                     ->with('author') // Eager load the author relationship
                     ->latest('published_at') // Order by the most recently published
                     ->paginate(9); // Show 9 posts per page

        return view('posts.index', compact('posts'));
    }

    /**
     * Display a single blog post.
     */
    public function show(Request $request, Post $post) 
    {
        // Ensure the post is published before showing it to the public
        if ($post->status !== 'published' || $post->published_at > now()) {
            abort(404);
        }
		$sessionKey = 'viewed_post_' . $post->id;
    if (!$request->session()->has($sessionKey)) {
        // Use increment() for an atomic and efficient database update
        $post->increment('views');
        // Store in the session immediately to prevent recount on refresh
        $request->session()->put($sessionKey, true);
    }

        // You might want to fetch recent posts for a sidebar here later
        $recentPosts = Post::where('status', 'published')
                           ->where('published_at', '<=', now())
                           ->where('id', '!=', $post->id) // Exclude the current post
                           ->latest('published_at')
                           ->take(5)
                           ->get();

        return view('posts.show', compact('post', 'recentPosts'));
    }
	 public function toggleLike(Request $request, Post $post)
    {
        $user = $request->user();

        // The toggle() method is a convenient Laravel helper for many-to-many.
        // It will attach if not attached, and detach if already attached.
        $user->likedPosts()->toggle($post->id);

        // Return a JSON response with the new like count.
        return response()->json([
            'success' => true,
            'is_liked' => $user->likedPosts()->where('post_id', $post->id)->exists(),
            'likes_count' => $post->likers()->count(),
        ]);
    }
}