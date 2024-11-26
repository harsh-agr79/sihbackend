<?php

namespace App\Http\Controllers;

use App\Models\JobListing;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class JobListingController extends Controller
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
}
