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




Route::group(['middleware'=>'api_key'], function () { 
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/email/verification-notification', [AuthController::class, 'sendVerificationEmail'])->name('verification.send');
    
    Route::post('/forgot-password', [AuthController::class, 'sendResetLinkEmail']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    

    Route::prefix('student')->group(function () {
        Route::middleware(['auth:student', 'verified'])->group(function () {
            Route::get('/profile', [StudentController::class, 'profile']);
            Route::post('/logout', [StudentController::class, 'logout']);

            Route::get('/getcourses', [CourseController::class, 'getCourses']);
            Route::get('/courses/details/{courseId}', [CourseController::class, 'getCourseDetails']);
            Route::post('/courses/enroll', [CourseController::class, 'enrollStudent']);
            Route::post('/course/assignment/submit', [CourseController::class, 'submitAssignment']);
            Route::post('/course/assignment/editsubmit', [CourseController::class, 'editSubmission']);

            Route::post('/job/apply', [JobController::class, 'applyToJobListing']);
        });
    });
    
    Route::prefix('mentor')->group(function () {
        Route::middleware(['auth:mentor', 'verified'])->group(function () {
            Route::get('/profile', [MentorController::class, 'profile']);
            Route::post('/logout', [MentorController::class, 'logout']);

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
        });
    });
    
    Route::prefix('company')->group(function () {
        Route::middleware(['auth:company', 'verified'])->group(function () {
            Route::get('/profile', [CompanyController::class, 'profile']);
            Route::post('/logout', [CompanyController::class, 'logout']);

            Route::post('/job/add', [JobController::class, 'createJobListing']);
        });
    });
    
    Route::prefix('teacher')->group(function () {
        Route::middleware(['auth:teacher', 'verified'])->group(function () {
            Route::get('/profile', [TeacherController::class, 'profile']);
            Route::post('/logout', [TeacherController::class, 'logout']);
        });
    });
    
    Route::prefix('institute')->group(function () {
        Route::middleware(['auth:institute', 'verified'])->group(function () {
            Route::get('/profile', [InstituteController::class, 'profile']);
            Route::post('/logout', [InstituteController::class, 'logout']);
        });
    });
});


