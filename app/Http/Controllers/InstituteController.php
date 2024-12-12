<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Teacher;

class InstituteController extends Controller
{
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function createTeacher( Request $request ) {
        $user = $request->user();

        $password = Str::random( 12 );
        $verificationToken = Str::random( 64 );
        $teacher = Teacher::create( [
            'institute_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make( $password ),
            'verification_token' => $verificationToken,
        ] );

        $verificationUrl = url( "https://sih.startuplair.com/api/email/verify/{$teacher->id}/teacher/{$verificationToken}" );
        $teacher->sendEmailVerificationNotifications( $verificationUrl );

        return response()->json( [ 'message' => "Teacher added ask student to verify their email and reset the password." ], 201 );
    }
}
