<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Mentor;
use App\Models\ModuleGroup;
use App\Models\Module;

class CourseController extends Controller {
    /**
    * Create a new course.
    *
    * @param Request $request
    * @return \Illuminate\Http\JsonResponse
    */

    public function createCourse( Request $request ) {
        // Retrieve the authenticated user
        $user = $request->user();

        // Ensure the user is authenticated and their type is 'mentor'
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        // Fetch the mentor record associated with the authenticated user
        $mentor = Mentor::find( $user->id );

        if ( !$mentor ) {
            return response()->json( [ 'error' => 'Mentor not found' ], 404 );
        }

        // Validate the incoming request data
        $validatedData = $request->validate( [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'level' => 'nullable|string|max:255',
            'domain_id' => 'required|exists:domains,id',
            'subdomains' => 'nullable|array',
            'subdomains.*' => 'exists:subdomains,id', // Validate each subdomain ID
        ] );

        try {
            // Create the course
            $course = Course::create( [
                'mentor_id' => $mentor->id, // Associate the mentor ID with the course
                'title' => $validatedData[ 'title' ],
                'description' => $validatedData[ 'description' ],
                'level' => $validatedData[ 'level' ],
                'domain_id' => $validatedData[ 'domain_id' ],
                'subdomains' => $validatedData[ 'subdomains' ] ?? [],
            ] );

            return response()->json( [
                'message' => 'Course created successfully',
                'course' => $course,
            ], 201 );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Failed to create course', 'details' => $e->getMessage() ], 500 );
        }
    }

    /**
    * Create a new module group within a course.
    *
    * @param Request $request
    * @param int $courseId
    * @return \Illuminate\Http\JsonResponse
    */

    public function createModuleGroup( Request $request ) {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $mentor = Mentor::find( $user->id );

        if ( !$mentor ) {
            return response()->json( [ 'error' => 'Mentor not found' ], 404 );
        }

        // Validate the request
        $validatedData = $request->validate( [
            'course_id' => 'required|exists:courses,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'position' => 'required|integer|min:1',
        ] );

        // Fetch the course and validate ownership
        $course = Course::where( 'id', $validatedData[ 'course_id' ] )
        ->where( 'mentor_id', $user->id )
        ->first();

        if ( !$course ) {
            return response()->json( [ 'error' => 'Course not found or you do not have permission to modify it' ], 404 );
        }

        try {
            // Create the module group
            $moduleGroup = ModuleGroup::create( [
                'course_id' => $course->id,
                'title' => $validatedData[ 'title' ],
                'description' => $validatedData[ 'description' ] ?? null,
                'position' => $validatedData[ 'position' ],
            ] );

            return response()->json( [
                'message' => 'Module group created successfully',
                'module_group' => $moduleGroup,
            ], 201 );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Failed to create module group', 'details' => $e->getMessage() ], 500 );
        }
    }

    /**
    * Create a new module within a course.
    *
    * @param Request $request
    * @return \Illuminate\Http\JsonResponse
    */

    public function createModule( Request $request ) {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $mentor = Mentor::find( $user->id );

        if ( !$mentor ) {
            return response()->json( [ 'error' => 'Mentor not found' ], 404 );
        }

        // Validate the request
        $validatedData = $request->validate( [
            'course_id' => 'required|exists:courses,id',
            'group_id' => 'nullable|exists:module_groups,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'video_url' => 'nullable|url',
            'transcript' => 'nullable|string',
            'material_links' => 'nullable|array',
            'material_links.*' => 'url', // Each link should be a valid URL
            'position' => 'required|integer|min:1',
        ] );

        // Fetch the course and validate ownership
        $course = Course::where( 'id', $validatedData[ 'course_id' ] )
        ->where( 'mentor_id', $user->id )
        ->first();

        if ( !$course ) {
            return response()->json( [ 'error' => 'Course not found or you do not have permission to modify it' ], 404 );
        }

        // If group_id is provided, validate that it belongs to the course
        if ( $validatedData[ 'group_id' ] ) {
            $groupExists = $course->moduleGroups()->where( 'id', $validatedData[ 'group_id' ] )->exists();
            if ( !$groupExists ) {
                return response()->json( [ 'error' => 'Invalid module group for the specified course' ], 400 );
            }
        }

        try {
            // Create the module
            $module = $course->modules()->create( [
                'group_id' => $validatedData[ 'group_id' ] ?? null,
                'title' => $validatedData[ 'title' ],
                'description' => $validatedData[ 'description' ],
                'video_url' => $validatedData[ 'video_url' ] ?? null,
                'transcript' => $validatedData[ 'transcript' ] ?? null,
                'material_links' => $validatedData[ 'material_links' ] ?? null,
                'position' => $validatedData[ 'position' ],
            ] );

            return response()->json( [
                'message' => 'Module created successfully',
                'module' => $module,
            ], 201 );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Failed to create module', 'details' => $e->getMessage() ], 500 );
        }
    }

}
