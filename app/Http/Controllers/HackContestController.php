<?php

namespace App\Http\Controllers;

use App\Models\HackContest;
use App\Models\Company;
use App\Models\Student;
use App\Models\HackathonRegistration;
use App\Models\HackathonSubmissions;
use Illuminate\Http\Request;

class HackContestController extends Controller {
    public function getCompanyHackContests( Request $request ) {
        // Retrieve the authenticated user
        $user = $request->user();

        // Ensure the user is authenticated and their type is 'company'
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        // Fetch the company record associated with the authenticated user
        $company = Company::find( $user->id );

        if ( !$company ) {
            return response()->json( [ 'error' => 'Company not found' ], 404 );
        }

        // Get all hackathon/contest events for the authenticated company, sorted by start date ( latest first )
        $hackContests = $company->hackContests()
        ->orderBy( 'start_date_time', 'desc' )
        ->get();

        return response()->json( [
            'message' => 'Hackathon/Contest events retrieved successfully',
            'hack_contests' => $hackContests,
        ] );
    }

    public function createHackContest( Request $request ) {
        $user = $request->user();

        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $company = Company::find( $user->id );

        if ( !$company ) {
            return response()->json( [ 'error' => 'Company not found' ], 404 );
        }

        $validated = $request->validate( [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'problem_statement' => 'nullable|string',
            'evaluation_criteria' => 'nullable|string',
            'eligibility' => 'nullable|string',
            'start_date_time' => 'required|date',
            'end_date_time' => 'required|date|after:start_date_time',
        ] );

        $hackContest = $company->hackContests()->create( $validated );

        return response()->json( [
            'message' => 'Hack contest created successfully',
            'hack_contest' => $hackContest,
        ], 201 );
    }

    public function getHackContest( $id, Request $request ) {
        $user = $request->user();

        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $hackContest = HackContest::where( 'company_id', $user->id )->find( $id );

        if ( !$hackContest ) {
            return response()->json( [ 'error' => 'Hack contest not found' ], 404 );
        }

        return response()->json( [
            'message' => 'Hack contest retrieved successfully',
            'hack_contest' => $hackContest,
        ] );
    }

    public function updateHackContest( Request $request, $id ) {
        $user = $request->user();

        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $hackContest = HackContest::where( 'company_id', $user->id )->find( $id );

        if ( !$hackContest ) {
            return response()->json( [ 'error' => 'Hack contest not found' ], 404 );
        }

        $validated = $request->validate( [
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'problem_statement' => 'nullable|string',
            'evaluation_criteria' => 'nullable|string',
            'eligibility' => 'nullable|string',
            'start_date_time' => 'nullable|date',
            'end_date_time' => 'nullable|date|after:start_date_time',
        ] );

        $hackContest->update( $validated );

        return response()->json( [
            'message' => 'Hack contest updated successfully',
            'hack_contest' => $hackContest,
        ] );
    }

    public function deleteHackContest( $id, Request $request ) {
        $user = $request->user();

        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $hackContest = HackContest::where( 'company_id', $user->id )->find( $id );

        if ( !$hackContest ) {
            return response()->json( [ 'error' => 'Hack contest not found' ], 404 );
        }

        $hackContest->delete();

        return response()->json( [
            'message' => 'Hack contest deleted successfully',
        ] );
    }

    public function registerForHackathon( Request $request, $hackContestId ) {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $student = Student::find( $user->id );

        if ( !$student ) {
            return response()->json( [ 'error' => 'Student not found' ], 404 );
        }

        $hackContest = HackContest::find( $hackContestId );

        if ( !$hackContest ) {
            return response()->json( [ 'error' => 'Hack contest not found' ], 404 );
        }

        $registration = HackathonRegistration::firstOrCreate( [
            'hack_contest_id' => $hackContestId,
            'student_id' => $user->id,
        ] );

        return response()->json( [
            'message' => 'Successfully registered for the hack contest',
            'registration' => $registration,
        ] );
    }

    public function submitForHackathon( Request $request, $hackContestId ) {
        $user = $request->user();

        // Ensure the user is authenticated and is a mentor
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $student = Student::find( $user->id );

        if ( !$student ) {
            return response()->json( [ 'error' => 'Student not found' ], 404 );
        }

        // Check if the student is registered for the hackathon
        $registration = HackathonRegistration::where( [
            [ 'hack_contest_id', '=', $hackContestId ],
            [ 'student_id', '=', $user->id ],
        ] )->first();

        if ( !$registration ) {
            return response()->json( [ 'error' => 'Not registered for this hack contest' ], 403 );
        }

        // Check if the student has already submitted
        $existingSubmission = $registration->submissions()->first();

        if ( $existingSubmission ) {
            return response()->json( [ 'error' => 'You have already submitted for this hack contest' ], 403 );
        }

        // Validate the submission
        $validated = $request->validate( [
            'description' => 'required|string',
            'link' => 'required|url',
        ] );

        // Create the submission
        $submission = $registration->submissions()->create( $validated );

        return response()->json( [
            'message' => 'Submission created successfully',
            'submission' => $submission,
        ] );
    }

    public function getHackathonSubmissions( $hackContestId, Request $request ) {
        $user = $request->user();

        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $company = Company::find( $user->id );

        if ( !$company ) {
            return response()->json( [ 'error' => 'Company not found' ], 404 );
        }

        $hackContest = HackContest::where( [
            [ 'id', '=', $hackContestId ],
            [ 'company_id', '=', $user->id ],
        ] )->first();

        if ( !$hackContest ) {
            return response()->json( [ 'error' => 'Hack contest not found or unauthorized' ], 404 );
        }

        $submissions = HackathonRegistration::where( 'hack_contest_id', $hackContestId )
        ->with( 'submissions' )
        ->get();

        return response()->json( [
            'message' => 'Submissions retrieved successfully',
            'submissions' => $submissions,
        ] );
    }

    public function getRegisteredHackContests( Request $request ) {
        $user = $request->user();

        // Ensure the user is authenticated
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $student = Student::find( $user->id );

        if ( !$student ) {
            return response()->json( [ 'error' => 'Student not found' ], 404 );
        }

        // Fetch registered hack contests with company details and associated submissions
        $hackContests = HackathonRegistration::where( 'student_id', $user->id )
        ->with( [ 'hackContest.company', 'submissions' ] ) // Include company details and submissions
        ->get();

        return response()->json( [
            'message' => 'Registered hack contests retrieved successfully',
            'hack_contests' => $hackContests,
        ] );
    }

    public function getUnregisteredHackContests( Request $request ) {
        $user = $request->user();

        // Ensure the user is authenticated
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $student = Student::find( $user->id );

        if ( !$student ) {
            return response()->json( [ 'error' => 'Student not found' ], 404 );
        }

        // Fetch IDs of hack contests the student has registered for
        $registeredHackContestIds = HackathonRegistration::where( 'student_id', $user->id )
        ->pluck( 'hack_contest_id' )
        ->toArray();

        // Fetch unregistered hack contests with company details
        $unregisteredHackContests = HackContest::whereNotIn( 'id', $registeredHackContestIds )
        ->with( 'company' ) // Include company details
        ->orderBy( 'created_at', 'desc' )
        ->get();

        return response()->json( [
            'message' => 'Unregistered hack contests retrieved successfully',
            'hack_contests' => $unregisteredHackContests,
        ] );
    }

}
