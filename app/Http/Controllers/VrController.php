<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Object3d;
use App\Models\Mentor;

class VrController extends Controller
{
    private function validateMentor(Request $request)
    {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if (!$user) {
            return response()->json(['error' => 'Unauthorized or invalid user type'], 403);
        }

        $mentor = Mentor::find($user->id);

        if (!$mentor) {
            return response()->json(['error' => 'Mentor not found'], 404);
        }

        return $mentor;
    }

    // Create (Upload)
    public function store(Request $request)
    {
        $mentor = $this->validateMentor($request);
        if ($mentor instanceof \Illuminate\Http\JsonResponse) {
            return $mentor; // Return the validation error response
        }
    
        // Validate input
        $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string',
            'file' => 'required|array',
            'file.*' => 'required|file|mimes:obj,fbx,glb,gltf|max:10240',
        ]);
    
        $savedObjects = [];
    
        // Loop through the name[] and file[] arrays
        foreach ($request->file('file') as $index => $file) {
            // Ensure a corresponding name exists
            if (!isset($request->name[$index])) {
                return response()->json(['error' => "Name is missing for file at index $index"], 400);
            }
    
            // Store file and save object
            $filePath = $file->store('3d_objects', 'public');
    
            $savedObjects[] = Object3d::create([
                'mentor_id' => $mentor->id,
                'name' => $request->name[$index],
                'file_path' => $filePath,
            ]);
        }
    
        return response()->json(['success' => true, 'data' => $savedObjects], 201);
    }
    
    

    // Read All
    public function index(Request $request)
    {
        $mentor = $this->validateMentor($request);
        if ($mentor instanceof \Illuminate\Http\JsonResponse) {
            return $mentor; // Return the validation error response
        }

        $objects = Object3d::where('mentor_id', $mentor->id)->get();
        return response()->json(['success' => true, 'data' => $objects]);
    }

    // Read Specific
    public function show(Request $request, $id)
    {
        $mentor = $this->validateMentor($request);
        if ($mentor instanceof \Illuminate\Http\JsonResponse) {
            return $mentor; // Return the validation error response
        }

        $object = Object3d::where('mentor_id', $mentor->id)->findOrFail($id);
        return response()->json(['success' => true, 'data' => $object]);
    }

    // Delete
    public function destroy(Request $request, $id)
    {
        $mentor = $this->validateMentor($request);
        if ($mentor instanceof \Illuminate\Http\JsonResponse) {
            return $mentor; // Return the validation error response
        }

        $object = Object3d::where('mentor_id', $mentor->id)->findOrFail($id);
        Storage::disk('public')->delete($object->file_path);
        $object->delete();

        return response()->json(['success' => true, 'message' => 'Resource deleted']);
    }    
}
