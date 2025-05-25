<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Platform;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PostController extends Controller
{


    //all users posts

    public function index(Request $request)
    {
        $query = Post::where('user_id', auth()->id())
            ->with('platforms');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('scheduled_time', $request->date);
        }

        return response()->json([
            'data' => $query->latest()->get()
        ]);
    }



    //create new post

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'scheduled_time' => 'required|date|after_or_equal:now',
            'platform_ids' => 'required|array',
            'platform_ids.*' => 'exists:platforms,id',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $imageUrl = $request->file('image')->store('post_images', 'public');
        }

        $post = Post::create([
            'title' => $validated['title'],
            'content' => $validated['content'],
            'image_url' => $imageUrl,
            'scheduled_time' => $validated['scheduled_time'],
            'status' => 'scheduled',
            'user_id' => auth()->id(),
        ]);

        $platforms = Platform::whereIn('id', $validated['platform_ids'])->get();

        $platformData = [];

        foreach ($platforms as $platform) {
            $this->validateForPlatforms($request->content, [$platform]);

            $platformData[$platform->id] = ['platform_status' => 'pending'];
        }

        $post->platforms()->attach($platformData);

        return response()->json([
            'message' => 'Post created and scheduled successfully.',
            'data' => $post->load('platforms'),
        ], 201);
    }


    //update post

    public function update(Request $request, Post $post)
    {
        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($post->status !== 'scheduled') {
            return response()->json(['message' => 'Only scheduled posts can be updated.'], 400);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'scheduled_time' => 'required|date|after_or_equal:now',
        ]);

        $post->update($validated);

        return response()->json([
            'message' => 'Post updated successfully.',
            'data' => $post,
        ]);
    }


    //delete post

    public function destroy(Post $post)
    {

        if (!isset($post)) {
            return response()->json(['message' => 'NotFound'], 404);
        }

        if ($post->user_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post deleted successfully.']);
    }


    //validation function

    public function validateForPlatforms($content, $platforms)
    {
        foreach ($platforms as $platform) {
            switch ($platform->type) {
                case 'twitter':
                    if (strlen($content) > 280) {
                        throw ValidationException::withMessages([
                            'content' => 'Content exceeds Twitter character limit (280).',
                        ]);
                    }
                    break;

                case 'linkedin':
                    if (strlen($content) > 1300) {
                        throw ValidationException::withMessages([
                            'content' => 'Content exceeds LinkedIn character limit (1300).',
                        ]);
                    }
                    break;
            }
        }
    }
}
