<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Mentor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Redirect;

class AuthController extends Controller {

    public function register( Request $request ) {
        $validator = Validator::make( $request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:student,mentor',
            'email' => 'required|string|email|max:255|unique:students,email|unique:mentors,email',
            'password' => 'required|string|min:8|regex:/[A-Za-z]/|regex:/[0-9]/|regex:/[@$!%*#?&]/',
        ] );

        if ( $validator->fails() ) {
            return response()->json( $validator->errors(), 422 );
        }

        $verificationToken = Str::random( 64 );

        if ( $request->type === 'student' ) {
            $user = Student::create( [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make( $request->password ),
                'verification_token' => $verificationToken,
            ] );
        } else if ( $request->type === 'mentor' ) {
            $user = Mentor::create( [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make( $request->password ),
                'verification_token' => $verificationToken,
            ] );
        }

        // Send Verification Email
        $verificationUrl = url( "https://sih.startuplair.com/api/email/verify/{$user->id}/{$request->type}/{$verificationToken}" );
        $user->sendEmailVerificationNotifications( $verificationUrl );

        return response()->json( [ 'message' => "{$request->type} registered successfully. Please verify your email." ], 201 );
    }

    public function login( Request $request ) {
        $request->validate( [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ] );

        $student = Student::where( 'email', $request->email )->first();
        $mentor = Mentor::where( 'email', $request->email )->first();
        $type = '';
        if ( $student ) {
            $user = $student;
            $type = 'student';
        } else if ( $mentor ) {
            $user = $mentor;
            $type = 'mentor';
        } else {
            $user = '';
            return response()->json( [ 'message' => 'Invalid credentials' ], 401 );
        }

        if ( !$user || !Hash::check( $request->password, $user->password ) ) {
            return response()->json( [ 'message' => 'Invalid credentials' ], 401 );
        }

        if ( !$user->hasVerifiedEmail() ) {
            return response()->json( [ 'message' => 'Please verify your email before logging in.' ], 403 );
        }

        $token = $user->createToken( 'student-token' )->plainTextToken;

        return response()->json( [ 'token' => $token, 'type'=>$type ], 200 );
    }

    public function sendResetLinkEmail( Request $request ) {
        $request->validate( [ 'email' => 'required|email' ] );

        $student = Student::where( 'email', $request->email )->first();
        $mentor = Mentor::where( 'email', $request->email )->first();

        if ( $student ) {
            $status = Password::broker( 'students' )->sendResetLink(
                $request->only( 'email' )
            );
        } else if ( $mentor ) {
            $status = Password::broker( 'mentors' )->sendResetLink(
                $request->only( 'email' )
            );
        } else {
            $status = '';
        }

        return $status === Password::RESET_LINK_SENT
        ? response()->json( [ 'message' => 'Reset link sent to your email.' ], 200 )
        : response()->json( [ 'message' => 'Unable to send reset link.' ], 400 );
    }

    public function resetPassword( Request $request ) {
        $request->validate( [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|confirmed|min:8',
        ] );

        $student_ = Student::where( 'email', $request->email )->first();
        $mentor_ = Mentor::where( 'email', $request->email )->first();

        if ( $student_ ) {

            $status = Password::broker( 'students' )->reset(
                $request->only( 'email', 'password', 'password_confirmation', 'token' ),

                function ( $student, $password ) {
                    $student->forceFill( [
                        'password' => Hash::make( $password ),
                    ] )->save();
                }
            );
        } else if ( $mentor_ ) {
            $status = Password::broker( 'mentors' )->reset(
                $request->only( 'email', 'password', 'password_confirmation', 'token' ),

                function ( $mentor, $password ) {
                    $mentor->forceFill( [
                        'password' => Hash::make( $password ),
                    ] )->save();
                }
            );
        } else {
            $status = '';
        }

        return $status === Password::PASSWORD_RESET
        ? response()->json( [ 'message' => 'Password reset successful.' ], 200 )
        : response()->json( [ 'message' => 'Invalid token or email.' ], 400 );
    }

    public function verifyEmail( Request $request, $id, $type, $hash ) {
        if($type == "student"){
            $user = Student::find( $id );
        }
        else if($type == "student"){
            $user =  Mentor::find( $id );
        }
        else{
            $user = NULL;
            return response()->json( [ 'message' => 'Invalid or expired verification link.' ], 400 );
        }

        if ( !$user || $hash != $user->verification_token ) {
            return response()->json( [ 'message' => 'Invalid or expired verification link.' ], 400 );
        }

        if ( $user->hasVerifiedEmail() ) {
            return Redirect::to( env( 'FRONTEND_URL' ) . '/email-already-verified' );
        }

        if ($user->markEmailAsVerified()) {
            $user->forceFill([
                'verification_token' => Str::random(128)
            ])->save();
            event(new \Illuminate\Auth\Events\Verified($user));
        }

        return Redirect::to( env( 'FRONTEND_URL' ) . '/email-verified' );
    }

}

