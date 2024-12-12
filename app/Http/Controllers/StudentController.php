<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;

class StudentController extends Controller
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
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        return response()->json($student);
    }

    public function updateProfile(Request $request)
    {
        $student =  $request->user();
    
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'profile_image' => 'nullable|image|max:2048',
        ]);
    
        if ($request->hasFile('profile_image')) {
            $path = $request->file('profile_student_image')->store('profiles', 'public');
            $student->image = $path;
        }
    
        $student->name = $validated['name'];
        $student->save();
    
        return response()->json($student);
    }
    
    public function updateEducation(Request $request)
    {
        $student =  $request->user();
    
        $validated = $request->validate([
            'education' => 'required|array',
        ]);
    
        $student->education = $validated['education'];
        $student->save();
    
        return response()->json($student);
    }
    
    // Similar functions for Experience, Skills, Hobbies, and Domains
    public function updateExperience(Request $request)
    {
        $student =  $request->user();
        $validated = $request->validate(['experience' => 'required|array']);
        $student->experience = $validated['experience'];
        $student->save();
        return response()->json($student);
    }
    
    public function updateSkills(Request $request)
    {
        $student =  $request->user();
        $validated = $request->validate(['skills' => 'required|array']);
        $student->skills = $validated['skills'];
        $student->save();
        return response()->json($student);
    }
    
    public function updateHobbies(Request $request)
    {
        $student =  $request->user();
        $validated = $request->validate(['hobbies' => 'required|array']);
        $student->hobbies = $validated['hobbies'];
        $student->save();
        return response()->json($student);
    }
    
    public function updateDomains(Request $request)
    {
        $student =  $request->user();
        $validated = $request->validate(['domains' => 'required|array|max:5']);
        $student->domains = $validated['domains'];
        $student->save();
        return response()->json($student);
    }
    
   
    
}
