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
        });
    });
    
    Route::prefix('mentor')->group(function () {
        Route::middleware(['auth:mentor', 'verified'])->group(function () {
            Route::get('/profile', [MentorController::class, 'profile']);
            Route::post('/logout', [MentorController::class, 'logout']);

            Route::post('/course/add', [CourseController::class, 'createCourse']);
            Route::post('/course/modulegroup/add', [CourseController::class, 'createModuleGroup']);
            Route::post('/course/module/add', [CourseController::class, 'createModule']);
        });
    });
    
    Route::prefix('company')->group(function () {
        Route::middleware(['auth:company', 'verified'])->group(function () {
            Route::get('/profile', [CompanyController::class, 'profile']);
            Route::post('/logout', [CompanyController::class, 'logout']);
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


