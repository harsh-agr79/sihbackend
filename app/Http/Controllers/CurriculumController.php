<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Institute;
use App\Models\Course;
use App\Models\Subdomain;

class CurriculumController extends Controller
{
    public function saveCurriculum(Request $request, $institutionId)
    {
        // Validate the incoming request
        $request->validate([
            'curriculum' => 'required|array',
            'curriculum.*.class_number' => 'required|integer|between:1,12', // Ensure class numbers are between 1 and 12
            'curriculum.*.domains' => 'required|array', // Ensure domains is an array
            'curriculum.*.level' => 'required|string|in:Beginner,Intermediate,Advanced', // Ensure valid levels
        ]);
    
        // Find the institution by ID
        $institution = Institute::findOrFail($institutionId);
    
        // Prepare curriculum data for storage
        $structuredCurriculum = [];
    
        foreach ($request->curriculum as $classData) {
            $structuredCurriculum[$classData['class_number']] = [
                'domains' => $classData['domains'],
                'level' => $classData['level'],
            ];
        }
    
        // Save the curriculum in the institution's curriculum field
        $institution->curriculum = $structuredCurriculum;
        $institution->save();
    
        return response()->json([
            'message' => 'Curriculum saved successfully!',
            'curriculum' => $structuredCurriculum,
        ]);
    }
    
    public function getCurriculum($institutionId)
    {
        // Find the institution by ID
        $institution = Institute::findOrFail($institutionId);

        // Return the curriculum field
        return response()->json([
            'message' => 'Curriculum retrieved successfully!',
            'curriculum' => $institution->curriculum,
        ]);
    }

    public function getFilteredCourses(Request $request, $gradeId)
    {
        try {
            // Get the authenticated user (Institute)
            $user = $request->user();
    
            // Ensure the user is authenticated and is an Institute
            if (!$user || !$user instanceof Institute) {
                return response()->json(['error' => 'Unauthorized or invalid user type'], 403);
            }
    
            // Retrieve the curriculum for the specified grade
            $curriculum = $user->curriculum[$gradeId] ?? null;
    
            if (!$curriculum) {
                return response()->json(['error' => 'No curriculum data found for this grade'], 404);
            }
    
            $domains = $curriculum['domains'];
            $level = $curriculum['level'];
            $approvedCourses = $curriculum['approved_courses'] ?? [];
    
            // Fetch courses filtered by domains and level
            $courses = Course::whereIn('domain_id', $domains)
                ->where('level', $level)
                ->where('verified', 1)
                ->with(['domain']) // Assuming Domain relationship is defined
                ->withCount([
                    'enrollments as enrolled' => function ($query) {
                        $query->whereNull('completed_at'); // Count currently enrolled students
                    },
                    'enrollments as completed' => function ($query) {
                        $query->whereNotNull('completed_at'); // Count completed students
                    },
                ])
                ->get();
    
            // Format the data
            $formattedCourses = $courses->map(function ($course) use ($approvedCourses) {
                // Get subdomain data
                $subdomainIds = $course->subdomains ?? [];
                $subdomainNames = Subdomain::whereIn('id', $subdomainIds)->get(['id', 'name']);
    
                return [
                    'id' => $course->id,
                    'title' => $course->title,
                    'description' => $course->description,
                    'verified' => $course->verified,
                    'level' => $course->level,
                    'domain_id' => $course->domain_id,
                    'domain_name' => $course->domain->name ?? null, // Include domain name
                    'subdomains' => $subdomainNames->map(function ($subdomain) {
                        return [
                            'id' => $subdomain->id,
                            'name' => $subdomain->name,
                        ];
                    }),
                    'created_at' => $course->created_at,
                    'updated_at' => $course->updated_at,
                    'completed' => $course->completed,
                    'enrolled' => $course->enrolled,
                    'is_selected' => in_array($course->id, $approvedCourses), // Check if the course is already approved
                ];
            });
    
            return response()->json([
                'message' => 'Filtered courses retrieved successfully',
                'courses' => $formattedCourses,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch courses.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
    
    
    public function toggleCourseSelection(Request $request, $gradeId)
   {
    try {
        // Get the authenticated user (Institute)
        $user = $request->user();

        // Ensure the user is authenticated and is an Institute
        if (!$user || !$user instanceof Institute) {
            return response()->json(['error' => 'Unauthorized or invalid user type'], 403);
        }

        // Validate the course ID
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $courseId = $validated['course_id'];

        // Retrieve the curriculum for the specified grade
        $curriculum = $user->curriculum;

        if (!isset($curriculum[$gradeId])) {
            return response()->json(['error' => 'No curriculum data found for this grade'], 404);
        }

        // Get approved courses, or initialize as an empty array
        $approvedCourses = $curriculum[$gradeId]['approved_courses'] ?? [];

        if (in_array($courseId, $approvedCourses)) {
            // If already approved, remove the course
            $approvedCourses = array_filter($approvedCourses, fn($id) => $id != $courseId);
        } else {
            // Otherwise, add the course
            $approvedCourses[] = $courseId;
        }

        // Update the approved courses in the curriculum
        $curriculum[$gradeId]['approved_courses'] = array_values($approvedCourses); // Reset array keys

        // Save the updated curriculum back to the database
        $user->curriculum = $curriculum;
        $user->save();

        return response()->json([
            'message' => 'Course approval toggled successfully.',
            'approved_courses' => $approvedCourses,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Failed to toggle course approval.',
            'details' => $e->getMessage(),
        ], 500);
    }
   }

}
