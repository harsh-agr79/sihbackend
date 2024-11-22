<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller {
    public function register( Request $request ) {
        $validator = Validator::make( $request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:students',
            'password' => [ 'required', 'string', 'min:8', 'regex:/[A-Za-z]/', 'regex:/[0-9]/', 'regex:/[@$!%*#?&]/' ],
        ] );

        if ( $validator->fails() ) {
            return response()->json( $validator->errors(), 422 );
        }

        $student = Student::create( [
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make( $request->password ),
        ] );

        // Send verification email
        $student->sendEmailVerificationNotification();

        return response()->json( [ 'message' => 'Student registered successfully. Please verify your email.' ], 201 );
    }

    public function login( Request $request ) {
        $request->validate( [
            'email' => 'required|string|email',
            'password' => 'required|string',
        ] );

        $student = Student::where( 'email', $request->email )->first();

        if ( !$student || !Hash::check( $request->password, $student->password ) ) {
            return response()->json( [ 'message' => 'Invalid credentials' ], 401 );
        }

        if ( !$student->hasVerifiedEmail() ) {
            return response()->json( [ 'message' => 'Please verify your email before logging in.' ], 403 );
        }

        $token = $student->createToken( 'student-token' )->plainTextToken;

        return response()->json( [ 'token' => $token ], 200 );
    }

    public function sendResetLinkEmail( Request $request ) {
        $request->validate( [
            'email' => 'required|email',
        ] );

        $student = Student::where( 'email', $request->email )->first();

        if ( !$student ) {
            return response()->json( [ 'message' => "We can't find a user with that email address." ], 404 );
        }

        // Send reset link
        $status = Password::sendResetLink(
            [ 'email' => $request->email ]
        );

        return $status === Password::RESET_LINK_SENT
        ? response()->json( [ 'message' => 'Reset link sent to your email.' ] )
        : response()->json( [ 'message' => 'Unable to send reset link.' ], 500 );
    }

    public function reset( Request $request ) {
        $request->validate( [
            'email' => 'required|email',
            'token' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ] );

        $status = Password::reset(
            $request->only( 'email', 'password', 'password_confirmation', 'token' ),

            function ( $user, $password ) {
                $user->forceFill( [
                    'password' => Hash::make( $password ),
                ] )->save();
            }
        );

        return $status === Password::PASSWORD_RESET
        ? response()->json( [ 'message' => __( $status ) ], 200 )
        : response()->json( [ 'message' => __( $status ) ], 400 );
    }

    public function sendVerificationEmail( Request $request ) {
        if ( $request->user()->hasVerifiedEmail() ) {
            return response()->json( [ 'message' => 'Email is already verified.' ], 400 );
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json( [ 'message' => 'Verification email sent.' ], 200 );
    }

    public function verifyEmail( Request $request ) {
        $user = Student::find( $request->route( 'id' ) );

        if ( !$user ) {
            return response()->json( [ 'message' => 'User not found.' ], 404 );
        }

        if ( $user->hasVerifiedEmail() ) {
            return response()->json( [ 'message' => 'Email already verified.' ], 400 );
        }

        if ( $user->markEmailAsVerified() ) {
            event( new \Illuminate\Auth\Events\Verified( $user ) );
        }

        return response()->json( [ 'message' => 'Email verified successfully.' ], 200 );
    }

}

