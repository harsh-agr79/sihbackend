<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Company;
use App\Models\Student;
use App\Models\EventRegistration;
use Illuminate\Http\Request;

class EventController extends Controller {
    public function index( Request $request ) {
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

        // Get all events for the authenticated company, sorted by datetime ( latest first )
        $events = $company->events()
        ->orderBy( 'datetime', 'desc' )
        ->get();

        return response()->json( [
            'message' => 'Events retrieved successfully',
            'events' => $events,
        ] );
    }

    // Store a new event for the authenticated company

    public function store( Request $request ) {
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

        // Validate the request data
        $validated = $request->validate( [
            'type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'link' => 'required|url',
            'datetime' => 'required|date',
            'speaker' => 'required|string|max:255',
            'description' => 'required|string',
        ] );

        // Create a new event
        $event = $company->events()->create( $validated );

        return response()->json( [
            'message' => 'Event created successfully',
            'event' => $event,
        ], 201 );
    }

    // Retrieve a specific event for the authenticated company

    public function show( Request $request, $id ) {
        // Retrieve the authenticated user
        $user = $request->user();

        // Ensure the user is authenticated and their type is 'company'
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        // Fetch the event and ensure it belongs to the authenticated company
        $event = Event::where( 'id', $id )
        ->where( 'company_id', $user->id )
        ->first();

        if ( !$event ) {
            return response()->json( [ 'error' => 'Event not found or unauthorized' ], 404 );
        }

        return response()->json( [
            'message' => 'Event retrieved successfully',
            'event' => $event,
        ] );
    }

    // Update a specific event for the authenticated company

    public function update( Request $request, $id ) {
        // Retrieve the authenticated user
        $user = $request->user();

        // Ensure the user is authenticated and their type is 'company'
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        // Fetch the event and ensure it belongs to the authenticated company
        $event = Event::where( 'id', $id )
        ->where( 'company_id', $user->id )
        ->first();

        if ( !$event ) {
            return response()->json( [ 'error' => 'Event not found or unauthorized' ], 404 );
        }

        // Validate the request data
        $validated = $request->validate( [
            'type' => 'sometimes|string|max:255',
            'title' => 'sometimes|string|max:255',
            'link' => 'sometimes|url',
            'datetime' => 'sometimes|date',
            'speaker' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
        ] );

        // Update the event
        $event->update( $validated );

        return response()->json( [
            'message' => 'Event updated successfully',
            'event' => $event,
        ] );
    }

    // Delete a specific event for the authenticated company

    public function destroy( Request $request, $id ) {
        // Retrieve the authenticated user
        $user = $request->user();

        // Ensure the user is authenticated and their type is 'company'
        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        // Fetch the event and ensure it belongs to the authenticated company
        $event = Event::where( 'id', $id )
        ->where( 'company_id', $user->id )
        ->first();

        if ( !$event ) {
            return response()->json( [ 'error' => 'Event not found or unauthorized' ], 404 );
        }

        // Delete the event
        $event->delete();

        return response()->json( [ 'message' => 'Event deleted successfully' ] );
    }

    public function registerStudentForEvent( Request $request, $eventId ) {
        $user = $request->user();

        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $student = Student::find( $user->id );

        if ( !$student ) {
            return response()->json( [ 'error' => 'Student not found' ], 404 );
        }

        $event = Event::find( $eventId );

        if ( !$event ) {
            return response()->json( [ 'error' => 'Event not found' ], 404 );
        }

        $alreadyRegistered = EventRegistration::where( 'student_id', $student->id )
        ->where( 'event_id', $eventId )
        ->exists();

        if ( $alreadyRegistered ) {
            return response()->json( [ 'error' => 'Already registered for this event' ], 400 );
        }

        $registration = EventRegistration::create( [
            'student_id' => $student->id,
            'event_id' => $eventId,
        ] );

        return response()->json( [
            'message' => 'Successfully registered for the event',
            'registration' => $registration,
        ] );
    }

    // Unregister a student from an event

    public function unregisterStudentFromEvent( Request $request, $eventId ) {
        $user = $request->user();

        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $student = Student::find( $user->id );

        if ( !$student ) {
            return response()->json( [ 'error' => 'Student not found' ], 404 );
        }

        $registration = EventRegistration::where( 'student_id', $student->id )
        ->where( 'event_id', $eventId )
        ->first();

        if ( !$registration ) {
            return response()->json( [ 'error' => 'Not registered for this event' ], 404 );
        }

        $registration->delete();

        return response()->json( [ 'message' => 'Successfully unregistered from the event' ] );
    }

    public function getStudentUnregisteredEvents( Request $request ) {
        $user = $request->user();

        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $student = Student::find( $user->id );

        if ( !$student ) {
            return response()->json( [ 'error' => 'Student not found' ], 404 );
        }

        // Get IDs of events the student has already registered for
        $registeredEventIds = $student->eventRegistrations()->pluck( 'event_id' );

        // Fetch events the student has not registered for
        $unregisteredEvents = Event::whereNotIn( 'id', $registeredEventIds )
        ->orderBy( 'datetime', 'asc' )
        ->get();

        return response()->json( [
            'message' => 'Unregistered events retrieved successfully',
            'events' => $unregisteredEvents,
        ] );
    }

    // Fetch all events registered by the authenticated student

    public function getStudentRegisteredEvents( Request $request ) {
        $user = $request->user();

        if ( !$user ) {
            return response()->json( [ 'error' => 'Unauthorized or invalid user type' ], 403 );
        }

        $student = Student::find( $user->id );

        if ( !$student ) {
            return response()->json( [ 'error' => 'Student not found' ], 404 );
        }

        $registrations = $student->eventRegistrations()->with( 'event' )->get();

        return response()->json( [
            'message' => 'Registered events retrieved successfully',
            'registrations' => $registrations,
        ] );
    }
}
