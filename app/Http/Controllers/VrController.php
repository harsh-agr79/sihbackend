<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Object3d;
use App\Models\Mentor;
use App\Models\Environment;
use Illuminate\Support\Facades\Storage;

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
    
        // Validate inputs
        $request->validate([
            'name' => 'required|array',
            'name.*' => 'required|string',
            'file' => 'required|array',
            'file.*' => [
                'required',
                'file',
                'max:10240', // Max size: 10 MB
                'mimetypes:application/octet-stream,text/plain,model/gltf+json,model/gltf-binary', // Add relevant MIME types
            ],
        ]);
    
        $savedObjects = [];
    
        foreach ($request->file('file') as $index => $file) {
            // Ensure a corresponding name exists
            if (!isset($request->name[$index])) {
                return response()->json(['error' => "Name is missing for file at index $index"], 400);
            }
    
            // Extract the original extension
            $extension = $file->getClientOriginalExtension();
    
            // Generate a unique file name while maintaining the original extension
            $fileName = uniqid() . '.' . $extension;
    
            // Store the file
            $filePath = $file->storeAs('3d_objects', $fileName, 'public');
    
            // Save the record in the database
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

    public function createEnvironment(Request $request)
    {
        // Ensure the user is authenticated and is a mentor
        $mentor = $this->validateMentor($request);
        if ($mentor instanceof \Illuminate\Http\JsonResponse) {
            return $mentor; // Return the validation error response
        }

        // Validate input
        $request->validate([
            'title' => 'required|string|max:255',
            'object_ids' => 'required|array|max:4', // Max 4 objects
            'object_ids.*' => 'exists:object3ds,id', // Ensure IDs exist in the 3D objects table
        ]);

        // Store environment
        $environment = Environment::create([
            'title' => $request->title,
            'mentor_id' => $mentor->id, // Associate the mentor ID
            'object_ids' => $request->object_ids,
        ]);

        return response()->json(['success' => true, 'data' => $environment], 201);
    }

    public function getEnvironment($id)
    {
        $environment = Environment::findOrFail($id);
        $objects = Object3d::whereIn('id', $environment->object_ids)->get();

        return response()->json([
            'title' => $environment->title,
            'mentor_id' => $environment->mentor_id,
            'objects' => $objects->map(fn($object) => [
                'name' => $object->name,
                'file_path' => asset('storage/' . $object->file_path), // Generate public URL
            ]),
        ]);
    }

    public function getMentorEnvironments(Request $request)
    {
        // Fetch the logged-in mentor
        $mentor = $request->user();

        if (!$mentor) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Fetch environments associated with the mentor
        $environments = $mentor->environments()->with('objects')->get();

        // Map the response with environment details and associated 3D objects
        return response()->json([
            'success' => true,
            'data' => $environments->map(function ($environment) {
                return [
                    'id' => $environment->id,
                    'title' => $environment->title,
                    'objects' => $environment->objects->map(function ($object) {
                        return [
                            'id' => $object->id,
                            'name' => $object->name,
                            'file_path' => asset('storage/' . $object->file_path),
                        ];
                    }),
                    'created_at' => $environment->created_at,
                    'updated_at' => $environment->updated_at,
                ];
            }),
        ]);
    }


}
