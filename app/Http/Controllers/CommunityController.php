<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Mentor;
use App\Models\Community;

class CommunityController extends Controller
{
    public function CreateCommunity(Request $request)
    {
        // Ensure the user is authenticated
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized or invalid user type'], 403);
        }

        // Determine if the user is a Student or Mentor
        $student = Student::find($user->id);
        $mentor = Mentor::find($user->id);

        if (!$student && !$mentor) {
            return response()->json(['error' => 'User must be a valid Student or Mentor'], 403);
        }

        $relationship = $user instanceof Student ? 'students' : ($user instanceof Mentor ? 'mentors' : null);

        if (!$relationship) {
            return response()->json(['error' => 'User must be a valid Student or Mentor'], 403);
        }

        // Validate the request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
            'cover_photo' => 'nullable|image|max:2048',
        ]);

        // Save uploaded files if present
        if ($request->hasFile('profile_photo')) {
            $validated['profile_photo'] = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        if ($request->hasFile('cover_photo')) {
            $validated['cover_photo'] = $request->file('cover_photo')->store('cover_photos', 'public');
        }

        // Create the community
        $community = Community::create(array_merge($validated, [
            'creator_type' => get_class($user),
            'creator_id' => $user->id,
        ]));

        // Automatically add the creator as an admin member
        $community->$relationship()->attach($user->id, [
            'member_type' => get_class($user),
            'role' => 'admin',
            'joined_at' => now(),
        ]);

        return response()->json(['message' => 'Community created successfully', 'community' => $community]);

    }

    public function UpdateCommunity(Request $request, $id)
    {
         // Find the community
        $community = Community::find($id);

        if (!$community) {
            return response()->json(['error' => 'Community not found'], 404);
        }

        // Get the authenticated user
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Check if the user is an admin of this community
        $isAdmin = \DB::table('community_users')
        ->where('community_id', $community->id)
        ->where('member_id', $user->id)
        ->where('member_type', get_class($user)) // Dynamically match Student or Mentor
        ->where('role', 'admin')
        ->exists();

        if (!$isAdmin) {
            return response()->json(['error' => 'You are not authorized to update this community'], 403);
        }

        // Validate the incoming request
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'profile_photo' => 'nullable|image|max:2048',
            'cover_photo' => 'nullable|image|max:2048',
        ]);

        // return response()->json([ $request->post() ]);

        // Update each field explicitly
        if ($request->has('name')) {
            $community->name = $request->input('name');
        }

        if ($request->has('description')) {
            $community->description = $request->input('description');
        }

        if ($request->hasFile('profile_photo')) {
            // Delete the old profile photo if it exists
            if ($community->profile_photo) {
                \Storage::delete($community->profile_photo);
            }

            // Save the new profile photo
            $community->profile_photo = $request->file('profile_photo')->store('profile_photos', 'public');
        }

        if ($request->hasFile('cover_photo')) {
            // Delete the old cover photo if it exists
            if ($community->cover_photo) {
                \Storage::delete($community->cover_photo);
            }

            // Save the new cover photo
            $community->cover_photo = $request->file('cover_photo')->store('cover_photos', 'public');
        }

        // Save the updated community
        $community->save();

        // Return the updated community data
        return response()->json([
            'message' => 'Community updated successfully',
            'community' => $community,
        ]);
    }
    
    public function getCommunityDetails(Request $request, $id)
    {
        // Ensure the user is authenticated
        $user = $request->user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Fetch the community with its creator and members
        $community = Community::with('creator')->find($id);
    
        if (!$community) {
            return response()->json(['error' => 'Community not found'], 404);
        }
    
        // Format the creator's data
        $creator = $community->creator;
    
        $relationship = $community->creator_type === Student::class ? 'students' : 'mentors';
    
        // Fetch joined_at from the correct relationship
        $joinedAt = $community->$relationship()
            ->where('community_users.member_id', $community->creator->id)
            ->where('community_users.member_type', $community->creator_type)
            ->first()
            ->pivot
            ->joined_at ?? null;
    
        // Check if the current user is a community member
        $userRelationship = $user instanceof Student ? 'students' : ($user instanceof Mentor ? 'mentors' : null);
    
        $isMember = $userRelationship ? $community->$userRelationship()
            ->where('community_users.member_id', $user->id)
            ->where('community_users.member_type', get_class($user))
            ->exists() : false;
    
        // Determine button text based on membership status
        $buttonText = $isMember ? 'Leave' : 'Join';
    
        // Format the response
        $response = [
            'id' => $community->id,
            'name' => $community->name,
            'description' => $community->description,
            'profile_photo' => $community->profile_photo,
            'cover_photo' => $community->cover_photo,
            'created_at' => $community->created_at,
            'updated_at' => $community->updated_at,
            'creator' => [
                'id' => $creator->id,
                'type' => class_basename($community->creator_type), // Extract the class name (e.g., 'Student' or 'Mentor')
                'name' => $creator->name,
                'email' => $creator->email,
                'joined_at' => $joinedAt,
            ],
            'button_text' => $buttonText,
        ];
    
        return response()->json($response);
    }
    
    public function getSideBarCommunityDetails(Request $request, $id)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        // Fetch the community with its creator, members, and other required data
        $community = Community::with(['creator', 'students', 'mentors', 'posts'])->find($id);

        if (!$community) {
            return response()->json(['error' => 'Community not found'], 404);
        }

        // Count the members, posts, and discussions
        $membersCount = $community->students->count() + $community->mentors->count();
        $postsCount = $community->posts->count();

        // For simplicity, discussions are posts that have comments
        $discussionsCount = $community->posts()->has('comments')->count();

        // Fetch the mentors with their id, name, and profile_image
        $mentors = $community->mentors->map(function ($mentor) {
            return [
                'id' => $mentor->id,
                'name' => $mentor->name,
                'profile_image' => $mentor->profile_photo
                    ? asset('storage/' . $mentor->profile_photo)
                    : 'https://fortmyersradon.com/wp-content/uploads/2019/12/dummy-user-img-1.png',
            ];
        });

        // Format the response
        $response = [
            'description' => $community->description,
            'members_count' => $membersCount . ' Members',
            'posts_count' => $postsCount . ' Posts',
            'discussions_count' => $discussionsCount . ' Discussions',
            'creator' => [
                'name' => $community->creator->name,
            ],
            'mentors' => $mentors,
        ];

        return response()->json($response);
    }


    public function destroy(Request $request, $id)
    {
        // Find the community
        $community = Community::find($id);
    
        if (!$community) {
            return response()->json(['error' => 'Community not found'], 404);
        }
    
        // Get the authenticated user
        $user = $request->user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Check if the user is the creator of the community
        if ((int)$community->creator_id != (int)$user->id || $community->creator_type != get_class($user)) {
            return response()->json(['error' => 'Only the creator of the community can delete it'], 403);
        }
    
        // Delete related resources if necessary (optional)
        $community->posts()->delete();
        \DB::table('community_users')->where('community_id', $community->id)->delete();
    
        // Delete the community
        $community->delete();
    
        return response()->json(['message' => 'Community deleted successfully']);
    }
    
    public function joinCommunity(Request $request, $communityId)
    {
        // Ensure the user is authenticated
        $user = $request->user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Determine if the user is a Student or Mentor
        $relationship = $user instanceof Student ? 'students' : ($user instanceof Mentor ? 'mentors' : null);
    
        if (!$relationship) {
            return response()->json(['error' => 'User must be a valid Student or Mentor'], 403);
        }
    
        // Find the community
        $community = Community::find($communityId);
    
        if (!$community) {
            return response()->json(['error' => 'Community not found'], 404);
        }
    
        // Check if the user is already a member of the community
        $isMember = $community->$relationship()
            ->where('community_users.member_id', $user->id)
            ->where('community_users.member_type', get_class($user))
            ->exists();
    
        if ($isMember) {
            return response()->json(['error' => 'You are already a member of this community'], 409);
        }
    
        // Add the user to the community as a member
        $community->$relationship()->attach($user->id, [
            'member_type' => get_class($user),
            'role' => 'member',
            'joined_at' => now(),
        ]);
    
        return response()->json(['message' => 'You have successfully joined the community']);
    }
    
    public function leaveCommunity(Request $request, $communityId)
    {
        // Ensure the user is authenticated
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Determine if the user is a Student or Mentor
        $relationship = $user instanceof Student ? 'students' : ($user instanceof Mentor ? 'mentors' : null);

        if (!$relationship) {
            return response()->json(['error' => 'User must be a valid Student or Mentor'], 403);
        }

        // Find the community
        $community = Community::find($communityId);

        if (!$community) {
            return response()->json(['error' => 'Community not found'], 404);
        }

        // Check if the user is a member of the community
        $isMember = $community->$relationship()
            ->where('community_users.member_id', $user->id)
            ->where('community_users.member_type', get_class($user))
            ->exists();

        if (!$isMember) {
            return response()->json(['error' => 'You are not a member of this community'], 409);
        }

        // Remove the user from the community
        $community->$relationship()->detach($user->id);

        return response()->json(['message' => 'You have successfully left the community']);
    }
    
    public function postInCommunity(Request $request, $communityId)
    {
        // Ensure the user is authenticated
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Determine if the user is a Student or Mentor
        $relationship = $user instanceof Student ? 'students' : ($user instanceof Mentor ? 'mentors' : null);

        if (!$relationship) {
            return response()->json(['error' => 'User must be a valid Student or Mentor'], 403);
        }

        // Find the community
        $community = Community::find($communityId);

        if (!$community) {
            return response()->json(['error' => 'Community not found'], 404);
        }

        // Check if the user is a member of the community
        $isMember = $community->$relationship()
            ->where('community_users.member_id', $user->id)
            ->where('community_users.member_type', get_class($user))
            ->exists();

        if (!$isMember) {
            return response()->json(['error' => 'You are not a member of this community'], 403);
        }

        // Validate input
        $validator = \Validator::make($request->all(), [
            'caption' => 'nullable|string|max:255',
            'content' => 'nullable|file|max:20480', // Max 20MB for content like images or videos
            'original_post_id' => 'nullable|exists:posts,id', // For reposting
        ]);

        // Custom validation logic
        $validator->after(function ($validator) use ($request) {
            // Ensure either caption or content is provided if not a repost
            if (!$request->input('original_post_id') && !$request->filled('caption') && !$request->hasFile('content')) {
                $validator->errors()->add('caption', 'Either caption or content must be provided if not reposting.');
                $validator->errors()->add('content', 'Either caption or content must be provided if not reposting.');
            }

            // Ensure content is null if reposting
            if ($request->input('original_post_id') && $request->hasFile('content')) {
                $validator->errors()->add('content', 'Content must be null when reposting.');
            }
        });

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle content upload if provided and not a repost
        $contentPath = null;
        if (!$request->input('original_post_id') && $request->hasFile('content')) {
            $contentPath = $request->file('content')->store('posts', 'public');
        }

        // Create the post
        $post = $community->posts()->create([
            'author_type' => get_class($user),
            'author_id' => $user->id,
            'caption' => $request->input('caption'),
            'content' => $contentPath,
            'original_post_id' => $request->input('original_post_id'),
        ]);

        return response()->json(['message' => 'Post created successfully', 'post' => $post]);
    }

    public function deletePost(Request $request, $communityId, $postId)
    {
        // Ensure the user is authenticated
        $user = $request->user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Find the community
        $community = Community::find($communityId);
    
        if (!$community) {
            return response()->json(['error' => 'Community not found'], 404);
        }
    
        // Find the post
        $post = $community->posts()->find($postId);
    
        if (!$post) {
            return response()->json(['error' => 'Post not found in this community'], 404);
        }
    
        // Check if the user is a Student or Mentor
        $relationship = $user instanceof Student ? 'students' : ($user instanceof Mentor ? 'mentors' : null);
    
        if (!$relationship) {
            return response()->json(['error' => 'User must be a valid Student or Mentor'], 403);
        }
    
        // Check if the user is an admin
        $isAdmin = $community->$relationship()
            ->where('community_users.member_id', $user->id)
            ->where('community_users.member_type', get_class($user))
            ->where('community_users.role', 'admin')
            ->exists();
    
        // Check if the user is the author of the post
        $isAuthor = (int)$post->author_id === (int)$user->id && $post->author_type === get_class($user);
    
        // Only allow deletion if the user is either an admin or the post's author
        if (!$isAdmin && !$isAuthor) {
            return response()->json(['error' => 'You are not authorized to delete this post'], 403);
        }
    
        // Delete the post (cascade delete will handle comments and likes)
        $post->delete();
    
        return response()->json(['message' => 'Post deleted successfully']);
    }

    public function commentOnPost(Request $request, $communityId, $postId)
    {
        // Ensure the user is authenticated
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Find the community
        $community = Community::find($communityId);

        if (!$community) {
            return response()->json(['error' => 'Community not found'], 404);
        }

        // Find the post
        $post = $community->posts()->find($postId);

        if (!$post) {
            return response()->json(['error' => 'Post not found in this community'], 404);
        }

        // Determine if the user is a Student or Mentor
        $relationship = $user instanceof Student ? 'students' : ($user instanceof Mentor ? 'mentors' : null);

        if (!$relationship) {
            return response()->json(['error' => 'User must be a valid Student or Mentor'], 403);
        }

        // Check if the user is a member of the community
        $isMember = $community->$relationship()
            ->where('community_users.member_id', $user->id)
            ->where('community_users.member_type', get_class($user))
            ->exists();

        if (!$isMember) {
            return response()->json(['error' => 'You are not a member of this community'], 403);
        }

        // Validate the comment content
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
            'parent_comment_id' => 'nullable|exists:comments,id', // For replies
        ]);

        // Create the comment
        $comment = $post->comments()->create([
            'author_id' => $user->id,
            'author_type' => get_class($user),
            'content' => $validated['content'],
            'parent_comment_id' => $validated['parent_comment_id'] ?? null,
        ]);

        return response()->json(['message' => 'Comment added successfully', 'comment' => $comment]);
    }


    public function toggleLike(Request $request, $communityId, $postId)
    {
        // Ensure the user is authenticated
        $user = $request->user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Find the community
        $community = Community::find($communityId);
    
        if (!$community) {
            return response()->json(['error' => 'Community not found'], 404);
        }
    
        // Find the post
        $post = $community->posts()->find($postId);
    
        if (!$post) {
            return response()->json(['error' => 'Post not found in this community'], 404);
        }
    
        // Determine if the user is a Student or Mentor
        $relationship = $user instanceof Student ? 'students' : ($user instanceof Mentor ? 'mentors' : null);
    
        if (!$relationship) {
            return response()->json(['error' => 'User must be a valid Student or Mentor'], 403);
        }
    
        // Check if the user is a member of the community
        $isMember = $community->$relationship()
            ->where('community_users.member_id', $user->id)
            ->where('community_users.member_type', get_class($user))
            ->exists();
    
        if (!$isMember) {
            return response()->json(['error' => 'You are not a member of this community'], 403);
        }
    
        // Check if the user has already liked the post
        $like = $post->likes()
            ->where('liker_id', $user->id)
            ->where('liker_type', get_class($user))
            ->first();
    
        if ($like) {
            // Unlike the post if already liked
            $like->delete();
    
            // Return the updated like count and success message
            return response()->json([
                'message' => 'Post unliked successfully',
                'like_count' => $post->likes()->count(),
                'is_liked_by_user' => false, // User no longer likes the post
            ]);
        } else {
            // Like the post if not already liked
            $post->likes()->create([
                'liker_id' => $user->id,
                'liker_type' => get_class($user),
            ]);
    
            // Return the updated like count and success message
            return response()->json([
                'message' => 'Post liked successfully',
                'like_count' => $post->likes()->count(),
                'is_liked_by_user' => true, // User now likes the post
            ]);
        }
    }
    
    
    
    public function getCommunityPosts(Request $request, $communityId)
    {
        // Ensure the user is authenticated
        $user = $request->user();
    
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
    
        // Find the community
        $community = Community::find($communityId);
    
        if (!$community) {
            return response()->json(['error' => 'Community not found'], 404);
        }
    
        // Determine if the user is a Student or Mentor
        $relationship = $user instanceof Student ? 'students' : ($user instanceof Mentor ? 'mentors' : null);
    
        if (!$relationship) {
            return response()->json(['error' => 'User must be a valid Student or Mentor'], 403);
        }
    
        // Check if the user is a member of the community
        $isMember = $community->$relationship()
            ->where('community_users.member_id', $user->id)
            ->where('community_users.member_type', get_class($user))
            ->exists();
    
        if (!$isMember) {
            return response()->json(['error' => 'You are not a member of this community'], 403);
        }
    
        // Fetch paginated posts, ordered by latest on top
        $posts = $community->posts()
            ->withCount('likes') // Count likes
            ->with(['likes' => function ($query) use ($user) {
                $query->where('liker_id', $user->id)
                    ->where('liker_type', get_class($user));
            }]) // Check if the user has liked each post
            ->with(['comments.author']) // Include comments and their authors
            ->with('author') // Include post author
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    
        // Format the posts
        $posts->getCollection()->transform(function ($post) use ($user) {
            return [
                'id' => $post->id,
                'community_id' => $post->community_id,
                'caption' => $post->caption,
                'content' => $post->content,
                'like_count' => $post->likes_count, // Total likes count
                'is_liked_by_user' => $post->likes->isNotEmpty(), // Whether the logged-in user has liked
                'username' => $post->author->name, // Post author's name
                'comments' => $post->comments->map(function ($comment) {
                    return [
                        'id' => $comment->id,
                        'post_id' => $comment->post_id,
                        'content' => $comment->content,
                        'username' => $comment->author->name, // Comment author's name
                        'created_at' => $comment->created_at,
                        'updated_at' => $comment->updated_at,
                    ];
                }),
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ];
        });
    
        return response()->json($posts);
    }
    


    public function getCommunityList(Request $request){
            // Validate optional filters
            $validated = $request->validate([
                'search' => 'nullable|string|max:255', // Search by community name or description
                'sort_by' => 'nullable|string|in:name,created_at', // Allow sorting by name or creation date
                'order' => 'nullable|string|in:asc,desc', // Sort order (ascending or descending)
                'per_page' => 'nullable|integer|min:1|max:100', // Number of results per page
            ]);
        
            // Query for communities
            $query = Community::query();
        
            // Apply search filter
            if (!empty($validated['search'])) {
                $query->where('name', 'like', '%' . $validated['search'] . '%')
                    ->orWhere('description', 'like', '%' . $validated['search'] . '%');
            }
        
            // Apply sorting
            $sortBy = $validated['sort_by'] ?? 'created_at'; // Default sorting by creation date
            $order = $validated['order'] ?? 'desc'; // Default order descending
            $query->orderBy($sortBy, $order);
        
            // Paginate results
            $perPage = $validated['per_page'] ?? 10; // Default 10 results per page
            $communities = $query->with('creator')->paginate($perPage);
        
            // Format response to include creator name
            $formattedCommunities = $communities->map(function ($community) {
                return [
                    'id' => $community->id,
                    'name' => $community->name,
                    'description' => $community->description,
                    'profile_photo' => $community->profile_photo,
                    'cover_photo' => $community->cover_photo,
                    'creator_name' => $community->creator ? $community->creator->name : 'Unknown',
                    'created_at' => $community->created_at,
                    'updated_at' => $community->updated_at,
                ];
            });
        
            return response()->json(
                $formattedCommunities
            );
    }
    
}
