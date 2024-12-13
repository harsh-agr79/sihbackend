<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class TeacherController extends Controller {
    public function profile( Request $request ) {
        return response()->json( $request->user() );
    }

    public function logout( Request $request ) {
        $request->user()->tokens()->delete();

        return response()->json( [ 'message' => 'Logged out successfully' ], 200 );
    }

    public function createStudent( Request $request ) {
        $user = $request->user();

        $password = Str::random( 12 );
        $verificationToken = Str::random( 64 );
        $student = Student::create( [
            'teacher_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'grade'=> $request->grade,
            'password' => Hash::make( $password ),
            'verification_token' => $verificationToken,
        ] );

        $verificationUrl = url( "https://sih.startuplair.com/api/email/verify/{$student->id}/student/{$verificationToken}" );
        $student->sendEmailVerificationNotifications( $verificationUrl );

        return response()->json( [ 'message' => "Student added ask student to verify their email and reset the password." ], 201 );
    }

    public function getStudents(Request $request) {
        $user = $request->user();

        // Fetch students associated with the teacher
        $students = Student::where('teacher_id', $user->id)->get();

        return response()->json($students, 200);
    }   
}
