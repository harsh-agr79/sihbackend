<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\Mentor;
use App\Models\Student;
use App\Models\ModuleGroup;
use App\Models\Module;
use App\Models\AssignmentQuiz;
use App\Models\Enrollment;
use App\Models\Submission;

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
    * Edit an existing course.
    *
    * @param Request $request
    * @param int $courseId
    * @return \Illuminate\Http\JsonResponse
    */

    public function editCourse( Request $request, $courseId ) {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $mentor = Mentor::find( $user->id );

        if ( !$mentor ) {
            return response()->json( [ 'error' => 'Mentor not found' ], 404 );
        }

        // Fetch the course and validate ownership
        $course = Course::where( 'id', $courseId )
        ->where( 'mentor_id', $user->id )
        ->first();

        if ( !$course ) {
            return response()->json( [ 'error' => 'Course not found or you do not have permission to modify it' ], 404 );
        }

        // Validate the incoming data
        $validatedData = $request->validate( [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'level' => 'nullable|string|max:255',
            'domain_id' => 'nullable|exists:domains,id',
            'subdomains' => 'nullable|array',
            'subdomains.*' => 'exists:subdomains,id',
        ] );

        try {
            $course->update( $validatedData );

            return response()->json( [
                'message' => 'Course updated successfully',
                'course' => $course,
            ], 200 );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Failed to update course', 'details' => $e->getMessage() ], 500 );
        }
    }

    /**
    * Delete a course.
    *
    * @param int $courseId
    * @return \Illuminate\Http\JsonResponse
    */

    public function deleteCourse( Request $request, $courseId ) {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $mentor = Mentor::find( $user->id );

        if ( !$mentor ) {
            return response()->json( [ 'error' => 'Mentor not found' ], 404 );
        }

        // Fetch the course and validate ownership
        $course = Course::where( 'id', $courseId )
        ->where( 'mentor_id', $user->id )
        ->first();

        if ( !$course ) {
            return response()->json( [ 'error' => 'Course not found or you do not have permission to delete it' ], 404 );
        }

        try {
            $course->delete();

            return response()->json( [ 'message' => 'Course deleted successfully' ], 200 );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Failed to delete course', 'details' => $e->getMessage() ], 500 );
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
    * Edit an existing module group.
    *
    * @param Request $request
    * @param int $moduleGroupId
    * @return \Illuminate\Http\JsonResponse
    */

    public function editModuleGroup( Request $request, $moduleGroupId ) {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $mentor = Mentor::find( $user->id );

        if ( !$mentor ) {
            return response()->json( [ 'error' => 'Mentor not found' ], 404 );
        }

        // Fetch the module group and validate ownership
        $moduleGroup = ModuleGroup::find( $moduleGroupId );

        if ( !$moduleGroup || ( int )$moduleGroup->course->mentor_id !== ( int )$user->id ) {
            return response()->json( [ 'error' => 'Module group not found or you do not have permission to modify it'.$user->id.$moduleGroup->course->mentor_id ], 404 );
        }

        // Validate the incoming data
        $validatedData = $request->validate( [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'position' => 'nullable|integer|min:1',
        ] );

        try {
            $moduleGroup->update( $validatedData );

            return response()->json( [
                'message' => 'Module group updated successfully',
                'module_group' => $moduleGroup,
            ], 200 );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Failed to update module group', 'details' => $e->getMessage() ], 500 );
        }
    }

    /**
    * Delete a module group.
    *
    * @param int $moduleGroupId
    * @return \Illuminate\Http\JsonResponse
    */

    public function deleteModuleGroup( Request $request, $moduleGroupId ) {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $mentor = Mentor::find( $user->id );

        if ( !$mentor ) {
            return response()->json( [ 'error' => 'Mentor not found' ], 404 );
        }

        // Fetch the module group and validate ownership
        $moduleGroup = ModuleGroup::find( $moduleGroupId );

        if ( !$moduleGroup || ( int )$moduleGroup->course->mentor_id !== ( int )$user->id ) {
            return response()->json( [ 'error' => 'Module group not found or you do not have permission to delete it' ], 404 );
        }

        try {
            $moduleGroup->delete();

            return response()->json( [ 'message' => 'Module group deleted successfully' ], 200 );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Failed to delete module group', 'details' => $e->getMessage() ], 500 );
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

    /**
    * Edit an existing module.
    *
    * @param Request $request
    * @param int $moduleId
    * @return \Illuminate\Http\JsonResponse
    */

    public function editModule( Request $request, $moduleId ) {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $mentor = Mentor::find( $user->id );

        if ( !$mentor ) {
            return response()->json( [ 'error' => 'Mentor not found' ], 404 );
        }

        // Fetch the module and validate ownership
        $module = Module::find( $moduleId );

        if ( !$module || ( int )$module->course->mentor_id !== ( int )$user->id ) {
            return response()->json( [ 'error' => 'Module not found or you do not have permission to modify it' ], 404 );
        }

        // Validate the incoming data
        $validatedData = $request->validate( [
            'group_id' => 'nullable|exists:module_groups,id',
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'video_url' => 'nullable|url',
            'transcript' => 'nullable|string',
            'material_links' => 'nullable|array',
            'material_links.*' => 'url',
            'position' => 'nullable|integer|min:1',
        ] );

        // If group_id is provided, validate that it belongs to the same course
        if ( $validatedData[ 'group_id' ] && $module->course->moduleGroups()->where( 'id', $validatedData[ 'group_id' ] )->doesntExist() ) {
            return response()->json( [ 'error' => 'Invalid module group for the specified course' ], 400 );
        }

        try {
            $module->update( $validatedData );

            return response()->json( [
                'message' => 'Module updated successfully',
                'module' => $module,
            ], 200 );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Failed to update module', 'details' => $e->getMessage() ], 500 );
        }
    }

    /**
    * Delete a module.
    *
    * @param int $moduleId
    * @return \Illuminate\Http\JsonResponse
    */

    public function deleteModule( Request $request, $moduleId ) {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $mentor = Mentor::find( $user->id );

        if ( !$mentor ) {
            return response()->json( [ 'error' => 'Mentor not found' ], 404 );
        }

        // Fetch the module and validate ownership
        $module = Module::find( $moduleId );

        if ( !$module || ( int )$module->course->mentor_id !== ( int )$user->id ) {
            return response()->json( [ 'error' => 'Module not found or you do not have permission to delete it' ], 404 );
        }

        try {
            $module->delete();

            return response()->json( [ 'message' => 'Module deleted successfully' ], 200 );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Failed to delete module', 'details' => $e->getMessage() ], 500 );
        }
    }

    /**
    * Create a new assignment or quiz for a module.
    *
    * @param Request $request
    * @return \Illuminate\Http\JsonResponse
    */

    public function createAssignmentQuiz( Request $request ) {
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
            'module_id' => 'required|exists:modules,id',
            'type' => 'required|in:assignment,quiz',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'content' => 'required|array', // Content as JSON
            'due_date' => 'nullable|date|after:now',
        ] );

        // Validate that the module belongs to a course owned by the mentor
        $module = Module::with( 'course' )->find( $validatedData[ 'module_id' ] );
        if ( !$module || ( int )$module->course->mentor_id !== ( int )$user->id ) {
            return response()->json( [ 'error' => 'Module not found or you do not have permission to modify it' ], 403 );
        }

        try {
            // Create the assignment or quiz
            $assignmentQuiz = $module->assignmentsQuizzes()->create( $validatedData );

            return response()->json( [
                'message' => 'Assignment/Quiz created successfully',
                'assignment_quiz' => $assignmentQuiz,
            ], 201 );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Failed to create assignment/quiz', 'details' => $e->getMessage() ], 500 );
        }
    }

    /**
    * Update an existing assignment or quiz.
    *
    * @param Request $request
    * @param int $assignmentQuizId
    * @return \Illuminate\Http\JsonResponse
    */

    public function updateAssignmentQuiz( Request $request, $assignmentQuizId ) {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $mentor = Mentor::find( $user->id );

        if ( !$mentor ) {
            return response()->json( [ 'error' => 'Mentor not found' ], 404 );
        }

        // Fetch the assignment/quiz and validate ownership
        $assignmentQuiz = AssignmentQuiz::with( 'module.course' )->find( $assignmentQuizId );

        if ( !$assignmentQuiz || (int)$assignmentQuiz->module->course->mentor_id !== (int)$user->id ) {
            return response()->json( [ 'error' => 'Assignment/Quiz not found or you do not have permission to modify it' ], 403 );
        }

        // Validate the request
        $validatedData = $request->validate( [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'content' => 'nullable|array', // Partial update for content
            'due_date' => 'nullable|date|after:now',
        ] );

        try {
            // Update the assignment or quiz
            $assignmentQuiz->update( $validatedData );

            return response()->json( [
                'message' => 'Assignment/Quiz updated successfully',
                'assignment_quiz' => $assignmentQuiz,
            ], 200 );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Failed to update assignment/quiz', 'details' => $e->getMessage() ], 500 );
        }
    }

    /**
    * Delete an assignment or quiz.
    *
    * @param int $assignmentQuizId
    * @return \Illuminate\Http\JsonResponse
    */

    public function deleteAssignmentQuiz( Request $request, $assignmentQuizId ) {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $mentor = Mentor::find( $user->id );

        if ( !$mentor ) {
            return response()->json( [ 'error' => 'Mentor not found' ], 404 );
        }

        // Fetch the assignment/quiz and validate ownership
        $assignmentQuiz = AssignmentQuiz::with( 'module.course' )->find( $assignmentQuizId );

        if ( !$assignmentQuiz || (int)$assignmentQuiz->module->course->mentor_id !== (int)$user->id ) {
            return response()->json( [ 'error' => 'Assignment/Quiz not found or you do not have permission to delete it' ], 403 );
        }

        try {
            // Delete the assignment or quiz
            $assignmentQuiz->delete();

            return response()->json( [ 'message' => 'Assignment/Quiz deleted successfully' ], 200 );
        } catch ( \Exception $e ) {
            return response()->json( [ 'error' => 'Failed to delete assignment/quiz', 'details' => $e->getMessage() ], 500 );
        }
    }


    /**
     * Get courses with enrolled and completed counts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCourses()
    {
        try {
            $courses = Course::with([
                'mentor:id,name', // Fetch mentor details
            ])
            ->withCount([
                'enrollments as enrolled' => function ($query) {
                    $query->whereNull('completed_at'); // Enrolled students
                },
                'enrollments as completed' => function ($query) {
                    $query->whereNotNull('completed_at'); // Completed students
                },
            ])
            ->get();

            // Transform the courses into the required format
            $formattedCourses = $courses->map(function ($course) {
                return [
                    'id' => $course->id,
                    'courseName' => $course->title,
                    'courseBy' => $course->mentor->name,
                    'completed' => $course->completed,
                    'enrolled' => $course->enrolled,
                ];
            });

            return response()->json($formattedCourses, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch courses', 'details' => $e->getMessage()], 500);
        }
    }

    /**
     * Get course details in a detailed format.
     *
     * @param int $courseId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCourseDetails($courseId)
    {
        try {
            // Fetch the course with relationships
            $course = Course::with([
                'mentor:id,name', // Fetch mentor details
                'moduleGroups.modules.assignmentsQuizzes' => function ($query) {
                    $query->select('id', 'module_id', 'type', 'title', 'description', 'content', 'due_date');
                }, // Fetch assignments within modules
                'ungroupedModules.assignmentsQuizzes' => function ($query) {
                    $query->select('id', 'module_id', 'type', 'title', 'description', 'content', 'due_date');
                }, // Fetch ungrouped modules and their assignments
            ])
            ->withCount([
                'enrollments as enrolled' => function ($query) {
                    $query->whereNull('completed_at'); // Enrolled students
                },
                'enrollments as completed' => function ($query) {
                    $query->whereNotNull('completed_at'); // Completed students
                },
            ])
            ->find($courseId);
    
            // If the course is not found
            if (!$course) {
                return response()->json(['error' => 'Course not found'], 404);
            }
    
            // Format the course data
            $formattedCourse = [
                'id' => $course->id,
                'courseName' => $course->title,
                'courseBy' => $course->mentor->name,
                'completed' => $course->completed,
                'enrolled' => $course->enrolled,
                'description' => $course->description,
                'module_groups' => $course->moduleGroups->map(function ($group) {
                    return [
                        'id' => $group->id,
                        'title' => $group->title,
                        'description' => $group->description,
                        'position' => $group->position,
                        'modules' => $group->modules->map(function ($module) {
                            return [
                                'id' => $module->id,
                                'module_name' => $module->title,
                                'video_url' => $module->video_url,
                                'description' => $module->description,
                                'transcript' => $module->transcript,
                                'material_links' => $module->material_links,
                                'position' => $module->position,
                                'assignments' => $module->assignmentsQuizzes->map(function ($assignment) {
                                    return [
                                        'id' => $assignment->id,
                                        'type' => $assignment->type,
                                        'title' => $assignment->title,
                                        'description' => $assignment->description,
                                        'content' => $assignment->content,
                                        'due_date' => $assignment->due_date,
                                    ];
                                }),
                            ];
                        }),
                    ];
                }),
                'ungrouped_modules' => $course->ungroupedModules->map(function ($module) {
                    return [
                        'id' => $module->id,
                        'module_name' => $module->title,
                        'video_url' => $module->video_url,
                        'description' => $module->description,
                        'transcript' => $module->transcript,
                        'material_links' => $module->material_links,
                        'position' => $module->position,
                        'assignments' => $module->assignmentsQuizzes->map(function ($assignment) {
                            return [
                                'id' => $assignment->id,
                                'type' => $assignment->type,
                                'title' => $assignment->title,
                                'description' => $assignment->description,
                                'content' => $assignment->content,
                                'due_date' => $assignment->due_date,
                            ];
                        }),
                    ];
                }),
            ];
    
            return response()->json($formattedCourse, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch course details', 'details' => $e->getMessage()], 500);
        }
    }
    

    public function enrollStudent(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);
    
        // Get the authenticated student (ensure the correct auth guard is used)
       

        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $student = Student::find( $user->id );

        if ( !$student ) {
            return response()->json( [ 'error' => 'Student not found' ], 404 );
        }
    
        // Check if the course exists and is verified
        $course = Course::where('id', $validated['course_id'])
            ->where('verified', true)
            ->first();
    
        if (!$course) {
            return response()->json(['error' => 'Course not found or not verified.'], 404);
        }
    
        // Check if the student is already enrolled in the course
        $existingEnrollment = Enrollment::where('student_id', $student->id)
            ->where('course_id', $course->id)
            ->first();
    
        if ($existingEnrollment) {
            return response()->json(['error' => 'You are already enrolled in this course.'], 400);
        }
    
        // Enroll the student in the course
        $enrollment = Enrollment::create([
            'student_id' => $student->id,
            'course_id' => $course->id,
            'enrollment_date' => now(),
        ]);
    
        return response()->json([
            'message' => 'You have been successfully enrolled in the course.',
            'enrollment' => $enrollment,
        ], 201);
    }
    public function submitAssignment(Request $request)
    {
        // Validate the request
        $validated = $request->validate([
            'assignment_id' => 'required|exists:assignment_quizzes,id',
            'submission_content' => 'required|array',
            'submission_content.answers' => 'required|array',
            'submission_content.answers.*.id' => 'required|integer',
            'submission_content.answers.*.answer' => 'nullable|string',
            'submission_content.answers.*.file' => 'nullable|file|mimes:pdf,jpg,png,doc,docx,txt|max:2048',
        ]);

        // Get the authenticated student
        $student = $request->user();

        if (!$student) {
            return response()->json(['error' => 'Unauthorized access.'], 401);
        }

        // Fetch the assignment or quiz
        $assignment = AssignmentQuiz::find($validated['assignment_id']);

        if (!$assignment) {
            return response()->json(['error' => 'Assignment or Quiz not found.'], 404);
        }

        // Validate if the student is enrolled in the course to which the assignment belongs
        $course = $assignment->module->course; // Assuming the assignment belongs to a module, and module belongs to a course
        $isEnrolled = $course->enrollments()->where('student_id', $student->id)->exists();

        if (!$isEnrolled) {
            return response()->json(['error' => 'You are not enrolled in this course.'], 403);
        }

        // Check if the student has already submitted
        $existingSubmission = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', $student->id)
            ->first();

        if ($existingSubmission) {
            return response()->json(['error' => 'You have already submitted this assignment or quiz.'], 400);
        }

        // Process answers
        $answers = [];
        foreach ($validated['submission_content']['answers'] as $answer) {
            if ($assignment->type === 'assignment' && isset($answer['file'])) {
                $filePath = $answer['file']->store('submissions', 'public');
                $answers[] = [
                    'id' => $answer['id'],
                    'answer' => null,
                    'file_path' => $filePath,
                ];
            } else {
                $answers[] = [
                    'id' => $answer['id'],
                    'answer' => $answer['answer'] ?? null,
                    'file_path' => null,
                ];
            }
        }

        // Create the submission
        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'submission_content' => ['answers' => $answers],
            'submitted_at' => now(),
        ]);

        return response()->json([
            'message' => ucfirst($assignment->type) . ' submitted successfully.',
            'submission' => $submission,
        ], 201);
    }
    public function editSubmission(Request $request)
    {

        $validated = $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'submission_content' => 'required|array',
            'submission_content.answers' => 'required|array',
            'submission_content.answers.*.id' => 'required|integer',
            'submission_content.answers.*.answer' => 'nullable|string',
            'submission_content.answers.*.file' => 'nullable|file|mimes:pdf,jpg,png,doc,docx,txt|max:2048',
        ]);

       

        $student = $request->user();

        if (!$student) {
            return response()->json(['error' => 'Unauthorized access.'], 401);
        }

       

        $submission = Submission::where('id', $validated['submission_id'])
            ->where('student_id', $student->id)
            ->first();

        if (!$submission) {
            return response()->json(['error' => 'Submission not found.'], 404);
        }

        // Fetch the assignment
        $assignment = AssignmentQuiz::find($submission->assignment_id);

        if (!$assignment) {
            return response()->json(['error' => 'Assignment or Quiz not found.'], 404);
        }

        $course = $assignment->module->course; // Assuming the assignment belongs to a module, and module belongs to a course
        $isEnrolled = $course->enrollments()->where('student_id', $student->id)->exists();

        if (!$isEnrolled) {
            return response()->json(['error' => 'You are not enrolled in this course.'], 403);
        }

        // Process answers
        $answers = [];
        foreach ($validated['submission_content']['answers'] as $answer) {
            if ($assignment->type === 'assignment' && isset($answer['file'])) {
                $filePath = $answer['file']->store('submissions', 'public');
                $answers[] = [
                    'id' => $answer['id'],
                    'answer' => null,
                    'file_path' => $filePath,
                ];
            } else {
                $answers[] = [
                    'id' => $answer['id'],
                    'answer' => $answer['answer'] ?? null,
                    'file_path' => null,
                ];
            }
        }

        $submission->update([
            'submission_content' => ['answers' => $answers],
            'submitted_at' => now(),
        ]);

        return response()->json([
            'message' => ucfirst($assignment->type) . ' submission updated successfully.',
            'submission' => $submission,
        ]);
    }

}
