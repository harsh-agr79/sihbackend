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
            $validated['profile_photo'] = $request->file('profile_photo')->store('profile_photos');
        }

        if ($request->hasFile('cover_photo')) {
            $validated['cover_photo'] = $request->file('cover_photo')->store('cover_photos');
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
            $community->profile_photo = $request->file('profile_photo')->store('profile_photos');
        }

        if ($request->hasFile('cover_photo')) {
            // Delete the old cover photo if it exists
            if ($community->cover_photo) {
                \Storage::delete($community->cover_photo);
            }

            // Save the new cover photo
            $community->cover_photo = $request->file('cover_photo')->store('cover_photos');
        }

        // Save the updated community
        $community->save();

        // Return the updated community data
        return response()->json([
            'message' => 'Community updated successfully',
            'community' => $community,
        ]);
    }
    
    public function getCommunityDetails($id)
    {
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
    
}
