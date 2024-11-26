<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use App\Models\Company;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use App\Models\Student;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{
    /**
     * Create a new job listing.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createJobListing(Request $request)
    {
        // Retrieve the authenticated user
        $user = $request->user();

        // Ensure the user is authenticated and their type is 'company'
        if (!$user) {
            return response()->json(['error' => 'Unauthorized or invalid user type'], 403);
        }

        // Fetch the company record associated with the authenticated user
        $company = Company::find($user->id);

        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        try {
            // Validate the incoming request data
            $validatedData = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'type' => 'required|in:apprenticeship,volunteering,training,field_visit',
                'location' => 'nullable|string|max:255',
                'start_date' => 'nullable|date|after_or_equal:today',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'application_deadline' => 'required|date|after_or_equal:today',
                'domain_id' => 'required|exists:domains,id',
                'subdomains' => 'nullable|array',
                'subdomains.*' => 'exists:subdomains,id',
                'special_requirements' => 'nullable|string',
                'skills_required' => 'nullable|array',
                'skills_required.*' => 'string|max:255',
            ]);

            // Create the job listing
            $jobListing = JobListing::create([
                'company_id' => $company->id,
                'title' => $validatedData['title'],
                'description' => $validatedData['description'],
                'type' => $validatedData['type'],
                'location' => $validatedData['location'] ?? null,
                'start_date' => $validatedData['start_date'] ?? null,
                'end_date' => $validatedData['end_date'] ?? null,
                'application_deadline' => $validatedData['application_deadline'],
                'domain_id' => $validatedData['domain_id'],
                'subdomains' => $validatedData['subdomains'] ?? [],
                'special_requirements' => $validatedData['special_requirements'] ?? null,
                'skills_required' => $validatedData['skills_required'] ?? [],
            ]);

            return response()->json([
                'message' => 'Job listing created successfully',
                'job_listing' => $jobListing,
            ], 201);

        } catch (ValidationException $e) {
            // Return validation error messages
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Handle any other errors
            return response()->json([
                'error' => 'Failed to create job listing',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Allow a student to apply to a job listing.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $jobListingId
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyToJobListing(Request $request, $jobListingId)
    {
        // Retrieve the authenticated user
        $user = $request->user();

        // Ensure the user is authenticated and their type is 'student'
        if (!$user) {
            return response()->json(['error' => 'Unauthorized or invalid user type'], 403);
        }

        // Fetch the student record associated with the authenticated user
        $student = Student::find($user->id);

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        // Fetch the job listing
        $jobListing = JobListing::find($jobListingId);

        if (!$jobListing) {
            return response()->json(['error' => 'Job listing not found'], 404);
        }

        // Validate the incoming request data
        try {
            $validatedData = $request->validate([
                'cover_letter' => 'required|string',
                'additional_files.*' => 'nullable|file|mimes:pdf,doc,docx,png,jpg|max:2048', // Validate file types and size
            ]);

            // Check if the student has already applied to the job listing
            $existingApplication = Application::where('job_listing_id', $jobListing->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existingApplication) {
                return response()->json(['error' => 'You have already applied to this job listing'], 409);
            }

            // Handle file uploads
            $uploadedFiles = [];
            if ($request->hasFile('additional_files')) {
                foreach ($request->file('additional_files') as $file) {
                    // Store file in the `applications` directory
                    $path = $file->store('applications', 'public');
                    $uploadedFiles[] = $path;
                }
            }

            // Create the application
            $application = Application::create([
                'job_listing_id' => $jobListing->id,
                'student_id' => $student->id,
                'cover_letter' => $validatedData['cover_letter'],
                'additional_files' => $uploadedFiles,
                'status' => 'pending',
                'shortlisted' => false,
                'final_selected' => false,
            ]);

            return response()->json([
                'message' => 'Application submitted successfully',
                'application' => $application,
            ], 201);

        } catch (ValidationException $e) {
            // Return validation error messages
            return response()->json([
                'error' => 'Validation failed',
                'messages' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            // Handle any other errors
            return response()->json([
                'error' => 'Failed to submit application',
                'details' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Shortlist a candidate for a job listing.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $applicationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function shortlistCandidate(Request $request, $applicationId)
    {
        $user = $request->user();

        // Ensure the user is authenticated and their type is 'company'
        if (!$user) {
            return response()->json(['error' => 'Unauthorized or invalid user type'], 403);
        }

        // Fetch the company record associated with the authenticated user
        $company = Company::find($user->id);

        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        // Find the application
        $application = Application::find($applicationId);

        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        // Ensure the job listing belongs to the authenticated company
        if ((int)$application->jobListing->company_id !== (int)$company->id) {
            return response()->json(['error' => 'Unauthorized action'], 403);
        }

        // Mark the application as shortlisted
        $application->update([
            'status' => 'shortlisted',
            'shortlisted' => true,
        ]);

        return response()->json([
            'message' => 'Candidate successfully shortlisted',
            'application' => $application,
        ]);
    }

    /**
     * Final select a candidate for a job listing.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $applicationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function selectCandidate(Request $request, $applicationId)
    {
        $user = $request->user();

        // Ensure the user is authenticated and their type is 'company'
        if (!$user) {
            return response()->json(['error' => 'Unauthorized or invalid user type'], 403);
        }

        // Fetch the company record associated with the authenticated user
        $company = Company::find($user->id);

        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        // Find the application
        $application = Application::find($applicationId);

        if (!$application) {
            return response()->json(['error' => 'Application not found'], 404);
        }

        // Ensure the job listing belongs to the authenticated company
        if ((int)$application->jobListing->company_id !== (int)$company->id) {
            return response()->json(['error' => 'Unauthorized action'], 403);
        }

        // Ensure the candidate is already shortlisted before final selection
        if (!$application->shortlisted) {
            return response()->json(['error' => 'Candidate must be shortlisted before final selection'], 422);
        }

        // Mark the application as final selected
        $application->update([
            'status' => 'final_selected',
            'final_selected' => true,
        ]);

        return response()->json([
            'message' => 'Candidate successfully selected',
            'application' => $application,
        ]);
    }


    /**
     * Get all active job listings with application deadlines greater than or equal to today.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getActiveJobListings(Request $request)
    {
        // Get the current date
        $today = now()->toDateString();

        // Retrieve job listings where application_deadline is greater than or equal to today
        $activeJobs = JobListing::where('application_deadline', '>=', $today)
            ->get();

        return response()->json([
            'message' => 'Active job listings retrieved successfully',
            'jobs' => $activeJobs,
        ]);
    }

    /**
     * Get all job listings of the authenticated company sorted by application deadline (latest first).
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCompanyListings(Request $request)
    {
        // Retrieve the authenticated user
        $user = $request->user();

        // Ensure the user is authenticated and their type is 'company'
        if (!$user) {
            return response()->json(['error' => 'Unauthorized or invalid user type'], 403);
        }

        // Fetch the company record associated with the authenticated user
        $company = Company::find($user->id);

        if (!$company) {
            return response()->json(['error' => 'Company not found'], 404);
        }

        // Get all job listings for the authenticated company, sorted by application deadline (latest first)
        $jobListings = $company->jobListings()
            ->orderBy('application_deadline', 'desc')
            ->get();

        return response()->json([
            'message' => 'Job listings retrieved successfully',
            'job_listings' => $jobListings,
        ]);
    }
}
