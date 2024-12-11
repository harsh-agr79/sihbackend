<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class VideoUploadController extends Controller
{
    /**
     * Handle video upload and generate public URL using Node.js script.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        // Validate the request
        $request->validate([
            'filePath' => 'required|string', // File path should be provided
        ]);

        $filePath = $request->input('filePath');
        $scriptPath = base_path('videoupload.js'); // Path to the Node.js script

        // Execute the Node.js script
        $process = new Process(['node', $scriptPath, $filePath]);
        $process->run();

        // Check if the process was successful
        if (!$process->isSuccessful()) {
            return response()->json(['error' => $process->getErrorOutput()], 500);
        }

        // Parse the output from the Node.js script
        $output = json_decode($process->getOutput(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON output from script'], 500);
        }

        return response()->json($output);
    }
}
