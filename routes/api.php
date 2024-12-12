<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\MentorController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\InstituteController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\CommunityController;
use App\Http\Controllers\VrController;
use App\Http\Controllers\HackContestController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\VideoUploadController;
use App\Http\Controllers\CurriculumController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/email/verify/{id}/{type}/{hash}', [AuthController::class, 'verifyEmail'])->name('verification.verify');

Route::post('/upload-video', [VideoUploadController::class, 'upload']);

Route::get('/environments/{id}', [VrController::class, 'getEnvironment']);

Route::group(['middleware'=>'api_key'], function () { 
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])->name('verification.send');
    
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::get('/subdomains/{id}', [CourseController::class, 'getSubdomainsByDomainId']);
    Route::get('/domains', [CourseController::class, 'getDomains']);
    

    Route::prefix('student')->group(function () {
        Route::middleware(['auth:student', 'verified'])->group(function () {
            Route::get('/profile', [StudentController::class, 'profile']);
            Route::post('/logout', [StudentController::class, 'logout']);

            Route::get('/getcourses', [CourseController::class, 'getCourses']);
            Route::get('/courses/details/{courseId}', [CourseController::class, 'getCourseDetails']);
            Route::get('/mycourses/details/{courseId}', [CourseController::class, 'getMyCourseDetails']);
            Route::get('/mycourses', [CourseController::class, 'getStudentEnrolledCourses']);
            Route::post('/courses/enroll', [CourseController::class, 'enrollStudent']);
            Route::post('/course/assignment/submit', [CourseController::class, 'submitAssignment']);
            Route::post('/course/assignment/editsubmit', [CourseController::class, 'editSubmission']);

            Route::get('/jobs/active', [JobController::class, 'getActiveJobListings']);
            Route::get('/jobs/applied', [JobController::class, 'getAppliedJobs']);
            Route::post('/job/{id}/apply', [JobController::class, 'applyToJobListing']);

            Route::post('/community/create', [CommunityController::class, 'CreateCommunity']);
            Route::put('/community/update/{id}', [CommunityController::class, 'UpdateCommunity']);
            Route::delete('/community/delete/{id}', [CommunityController::class, 'destroy']);
            Route::get('/community/{id}', [CommunityController::class, 'getCommunityDetails']);
            Route::get('/community/sidebar/{id}', [CommunityController::class, 'getSideBarCommunityDetails']);

            Route::post('/community/{communityId}/join', [CommunityController::class, 'joinCommunity']);
            Route::delete('/community/{communityId}/leave', [CommunityController::class, 'leaveCommunity']);
            Route::post('/community/{communityId}/post', [CommunityController::class, 'postInCommunity']);
            Route::delete('/community/{communityId}/post/{postId}', [CommunityController::class, 'deletePost']);
            Route::post('/community/{communityId}/post/{postId}/like', [CommunityController::class, 'toggleLike']);
            Route::post('/community/{communityId}/post/{postId}/comment', [CommunityController::class, 'commentOnPost']);

            Route::get('/community/{communityId}/posts', [CommunityController::class, 'getCommunityPosts']);
            Route::get('/explorecommunity/list', [CommunityController::class, 'getCommunityList']);

            Route::get('/hack-contests/unregistered', [HackContestController::class, 'getUnregisteredHackContests']);
            Route::get('/hack-contests/registered', [HackContestController::class, 'getRegisteredHackContests']);
            Route::post('/hack-contests/{hackContestId}/register', [HackContestController::class, 'registerForHackathon']);
            Route::post('/hack-contests/{hackContestId}/submit', [HackContestController::class, 'submitForHackathon']);

            Route::get('/events/registered', [EventController::class, 'getStudentRegisteredEvents']);
            Route::get('/events/unregistered', [EventController::class, 'getStudentUnregisteredEvents']);
            Route::post('/events/{id}/register', [EventController::class, 'registerStudentForEvent']);
            Route::delete('/events/{id}/unregister', [EventController::class, 'unregisterStudentFromEvent']);
        });
    });
    
    Route::prefix('mentor')->group(function () {
        Route::middleware(['auth:mentor', 'verified'])->group(function () {
            Route::get('/profile', [MentorController::class, 'profile']);
            Route::post('/logout', [MentorController::class, 'logout']);

            Route::get('/course', [CourseController::Class, 'getCourseList']);
            Route::get('/course/{courseid}', [CourseController::Class, 'manageCourseDetails']);

            Route::post('/course/add', [CourseController::class, 'createCourse']);
            Route::post('/course/modulegroup/add', [CourseController::class, 'createModuleGroup']);
            Route::post('/course/module/add', [CourseController::class, 'createModule']);

            Route::put('/course/{courseId}', [CourseController::class, 'editCourse']); // For full updates
            Route::put('/course/modulegroup/{moduleGroupId}', [CourseController::class, 'editModuleGroup']);
            Route::put('/course/module/{moduleId}', [CourseController::class, 'editModule']);

            Route::delete('/course/{courseId}', [CourseController::class, 'deleteCourse']);
            Route::delete('/course/modulegroup/{moduleGroupId}', [CourseController::class, 'deleteModuleGroup']);
            Route::delete('/course/module/{moduleId}', [CourseController::class, 'deleteModule']);

            Route::post('/course/assignment-quizzes/add', [CourseController::class, 'createAssignmentQuiz']);
            Route::put('/course/assignment-quizzes/{assignmentQuizId}', [CourseController::class, 'updateAssignmentQuiz']);
            Route::delete('/course/assignment-quizzes/{assignmentQuizId}', [CourseController::class, 'deleteAssignmentQuiz']);

            Route::get('/community', [CommunityController::class, 'getMyCommunity']);
            Route::post('/community/create', [CommunityController::class, 'CreateCommunity']);
            Route::put('/community/update/{id}', [CommunityController::class, 'UpdateCommunity']);
            Route::delete('/community/delete/{id}', [CommunityController::class, 'destroy']);
            Route::get('/community/{id}', [CommunityController::class, 'getCommunityDetails']);
            Route::get('/community/sidebar/{id}', [CommunityController::class, 'getSideBarCommunityDetails']);

            Route::post('/community/{communityId}/join', [CommunityController::class, 'joinCommunity']);
            Route::delete('/community/{communityId}/leave', [CommunityController::class, 'leaveCommunity']);
            Route::post('/community/{communityId}/post', [CommunityController::class, 'postInCommunity']);
            Route::delete('/community/{communityId}/post/{postId}', [CommunityController::class, 'deletePost']);
            Route::post('/community/{communityId}/post/{postId}/like', [CommunityController::class, 'toggleLike']);
            Route::post('/community/{communityId}/post/{postId}/comment', [CommunityController::class, 'commentOnPost']);

            Route::get('/explorecommunity/list', [CommunityController::class, 'getCommunityList']);
            Route::get('/community/{communityId}/posts', [CommunityController::class, 'getCommunityPosts']);

            Route::post('/3d-objects', [VrController::class, 'store']);
            Route::get('/3d-objects', [VrController::class, 'index']);
            Route::get('/3d-objects/{id}', [VrController::class, 'show']);
            Route::delete('/3d-objects/{id}', [VrController::class, 'destroy']);

            Route::post('/environments', [VrController::class, 'createEnvironment']); 
            Route::get('/environments', [VrController::class, 'getMentorEnvironments']);
        });
    });
    
    Route::prefix('company')->group(function () {
        Route::middleware(['auth:company', 'verified'])->group(function () {
            Route::get('/profile', [CompanyController::class, 'profile']);
            Route::post('/logout', [CompanyController::class, 'logout']);

            Route::get('/job', [JobController::class, 'getCompanyListings']);
            Route::post('/job/add', [JobController::class, 'createJobListing']);
            Route::put('/job/{applicationId}/shortlist', [JobController::class, 'shortlistCandidate']);
            Route::put('/job/{applicationId}/select', [JobController::class, 'selectCandidate']);

            Route::get('/job/{jobListingId}/applicants/unprocessed', [JobController::class, 'getUnprocessedApplicants']);
            Route::get('/job/{jobListingId}/applicants/shortlisted', [JobController::class, 'getShortlistedApplicants']);
            Route::get('/job/{jobListingId}/applicants/final-selected', [JobController::class, 'getFinalSelectedApplicants']);

            Route::get('/hack-contests', [HackContestController::class, 'getCompanyHackContests']);
            Route::post('/hack-contests', [HackContestController::class, 'createHackContest']);
            Route::get('/hack-contests/{id}', [HackContestController::class, 'getHackContest']);
            Route::put('/hack-contests/{id}', [HackContestController::class, 'updateHackContest']);
            Route::delete('/hack-contests/{id}', [HackContestController::class, 'deleteHackContest']);

            Route::get('/hack-contests/{hackContestId}/submissions', [HackContestController::class, 'getHackathonSubmissions']);
            Route::post('/hack-contests/submissions/{submissionId}/evaluate', [HackContestController::class, 'evaluateSubmission']);

            Route::get('/events', [EventController::class, 'index']);
            Route::post('/events', [EventController::class, 'store']);
            Route::get('/events/{id}', [EventController::class, 'show']);
            Route::put('/events/{id}', [EventController::class, 'update']);
            Route::delete('/events/{id}', [EventController::class, 'destroy']);
        });
    });
    
    Route::prefix('teacher')->group(function () {
        Route::middleware(['auth:teacher', 'verified'])->group(function () {
            Route::get('/profile', [TeacherController::class, 'profile']);
            Route::post('/logout', [TeacherController::class, 'logout']);

            Route::get('/getcourses', [CourseController::class, 'getCourses']);
            Route::get('/courses/details/{courseId}', [CourseController::class, 'getCourseDetails']);
            Route::get('/mycourses/details/{courseId}', [CourseController::class, 'getMyCourseDetails']);
            Route::get('/mycourses', [CourseController::class, 'getStudentEnrolledCourses']);
            Route::post('/courses/enroll', [CourseController::class, 'enrollStudent']);
            Route::post('/course/assignment/submit', [CourseController::class, 'submitAssignment']);
            Route::post('/course/assignment/editsubmit', [CourseController::class, 'editSubmission']);

            Route::post('/createStudent', [TeacherController::class , 'createStudent']);
        });
    });
    
    Route::prefix('institute')->group(function () {
        Route::middleware(['auth:institute', 'verified'])->group(function () {
            Route::get('/profile', [InstituteController::class, 'profile']);
            Route::post('/logout', [InstituteController::class, 'logout']);

            Route::get('/{institutionId}/curriculum', [CurriculumController::class, 'getCurriculum']);
            Route::post('/{institutionId}/curriculum/update', [CurriculumController::class, 'saveCurriculum']);

            Route::get('/courses/filtered/{grade}', [CurriculumController::class, 'getFilteredCourses']);
            Route::post('/courses/toggle-approved/{gradeId}', [CurriculumController::class, 'toggleCourseSelection']);

            Route::post('/createTeacher', [TeacherController::class , 'createTeacher']);

        });
    });
});


