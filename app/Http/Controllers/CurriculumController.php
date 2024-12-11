<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Institute;

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

}
