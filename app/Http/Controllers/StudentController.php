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

    /**
     * Update the profile data of a student.
     */
    public function updateProfile(Request $request, $id)
    {
        $student = Student::find($id);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Validate incoming request
        $validatedData = $request->validate([
            'education' => 'nullable|array',
            'experience' => 'nullable|array',
            'skills' => 'nullable|array',
            'hobbies' => 'nullable|array',
            'domains' => 'nullable|array|max:5', // Max 5 domains
        ]);

        // Update fields if provided
        $student->education = $validatedData['education'] ?? $student->education;
        $student->experience = $validatedData['experience'] ?? $student->experience;
        $student->skills = $validatedData['skills'] ?? $student->skills;
        $student->hobbies = $validatedData['hobbies'] ?? $student->hobbies;
        $student->domains = $validatedData['domains'] ?? $student->domains;

        // Save changes
        $student->save();

        return response()->json(['message' => 'Profile updated successfully', 'student' => $student]);
    }
    
}
