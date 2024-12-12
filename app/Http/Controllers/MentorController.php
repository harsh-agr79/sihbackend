<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Model\Mentor;

class MentorController extends Controller
{
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function getProfile($id)
    {
        $mentor = Mentorfind($id);

        if (!$mentor) {
            return response()->json(['error' => 'Mentor not found'], 404);
        }

        return response()->json($mentor);
    }

    public function updateProfile(Request $request)
    {
        $mentor =  $request->user();
    
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'profile_image' => 'nullable|image|max:2048',
        ]);
    
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_mentor_image')->store('profiles', 'public');
            $mentor->image = $path;
        }
    
        $mentor->name = $validated['name'];
        $mentor->save();
    
        return response()->json($mentor);
    }
    
    public function updateEducation(Request $request)
    {
        $mentor =  $request->user();
    
        $validated = $request->validate([
            'education' => 'required|array',
        ]);
    
        $mentor->education = $validated['education'];
        $mentor->save();
    
        return response()->json($mentor);
    }
    
    // Similar functions for Experience, Skills, Hobbies, and Domains
    public function updateExperience(Request $request)
    {
        $mentor =  $request->user();
        $validated = $request->validate(['experience' => 'required|array']);
        $mentor->experience = $validated['experience'];
        $mentor->save();
        return response()->json($mentor);
    }
    
    public function updateSkills(Request $request)
    {
        $mentor =  $request->user();
        $validated = $request->validate(['skills' => 'required|array']);
        $mentor->skills = $validated['skills'];
        $mentor->save();
        return response()->json($mentor);
    }
    
    public function updateHobbies(Request $request)
    {
        $mentor =  $request->user();
        $validated = $request->validate(['hobbies' => 'required|array']);
        $mentor->hobbies = $validated['hobbies'];
        $mentor->save();
        return response()->json($mentor);
    }
    
    public function updateDomains(Request $request)
    {
        $mentor =  $request->user();
        $validated = $request->validate(['domains' => 'required|array|max:5']);
        $mentor->domains = $validated['domains'];
        $mentor->save();
        return response()->json($mentor);
    }
}
