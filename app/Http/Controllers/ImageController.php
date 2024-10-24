<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateMockImages;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Laravel\Facades\Image as InterventionImage;
use OpenAI;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'image' => 'required|image|mimes:jpg,png|max:2048',
            ]);

            // Store the image in S3
            $path = $request->file('image')->store('images', 's3');

            // Generate the full URL for the stored image
            $url = Storage::disk('s3')->url($path);

            // Create the image record in the database
            $image = Image::create([
                'filename' => $request->file('image')->getClientOriginalName(),
                'user_id' => Auth::id(),
                'url' => $url,
                'mime_type' => $request->file('image')->getMimeType(),
            ]);

            return response()->json(['image' => $image], 201);
        } catch (\Exception $e) {
            // Log the error message for debugging
            Log::error('Image upload failed: ' . $e->getMessage());

            // Return error details in response for debugging purposes
            return response()->json([
                'error' => 'Image upload failed.',
                'message' => $e->getMessage(),  // Return the error message
                'file' => $e->getFile(),        // Return the file where the error occurred
                'line' => $e->getLine(),        // Return the line number of the error
            ], 500);
        }
    }

    public function generateImageVariations($imageId)
    {
        try {
            // Fetch the image from the database
            $image = Image::findOrFail($imageId);

            // Use OpenAI's DALL·E or a similar service to create variations
            $yourApiKey = env('OPENAI_API_KEY');
            $client = OpenAI::client($yourApiKey);

            // Call the OpenAI API to generate variations
            $response = $client->images()->create([
                'prompt' => 'Generate variations of this image: ' . $image->url,
                'n' => 5, // Number of variations to create
                'size' => '1024x1024' // Specify size if needed
            ]);

            // Initialize an array to hold generated image URLs
            $generatedImages = [];
            foreach ($response->data as $data) {
                $generatedImageUrl = $data->url; // Adjust based on response structure

                // Download the image content
                $imageContent = file_get_contents($generatedImageUrl);
                if ($imageContent === false) {
                    throw new \Exception('Failed to download image from: ' . $generatedImageUrl);
                }

                // Create a unique filename for the generated image
                $filename = 'generated_' . uniqid() . '.png';

                // Store the generated image in S3
                $path = 'images/generated/' . $filename; // Define the path in S3
                Storage::disk('s3')->put($path, $imageContent); // Store the image

                // Generate the full URL for the stored image
                $s3Url = Storage::disk('s3')->url($path);

                // Save the generated image record in the database
                $generatedImages[] = [
                    'filename' => $filename,
                    'user_id' => Auth::id(), // Assuming you want to associate with the user
                    'url' => $s3Url,
                    'mime_type' => 'image/png' // Assuming all generated images are PNG
                ];
            }

            // Store all generated images in the database
            Image::insert($generatedImages);

            // Return a consistent response structure
            return response()->json([
                'success' => true,
                'variations' => array_column($generatedImages, 'url'),
                'message' => 'Image variations generated and stored successfully.'
            ], 200);
        } catch (\Exception $e) {
            // Log the error message for debugging
            Log::error('Image variation generation failed: ' . $e->getMessage());

            // Check for specific error message related to billing
            if (strpos($e->getMessage(), 'Billing hard limit has been reached') !== false) {
                // Dispatch the job to generate mocked images
                GenerateMockImages::dispatch(5); // Change the number based on how many mocked images you want

                // Return a response indicating the fallback action
                return response()->json([
                    'success' => true,
                    'variations' => [], // No variations generated from OpenAI
                    'message' => 'Billing hard limit has been reached for the given OpenAI API Key.For Demo Mocked images generation has been queued.'
                ], 200);
            }




            // Return a consistent error response structure
            return response()->json([
                'success' => false,
                'error' => 'Image variation generation failed.',
                'message' => $e->getMessage() // Return the error message
            ], 500);
        }
    }

    public function getUserImages()
    {
        // Fetch all images from the database
        $images = Image::all(['id', 'filename', 'url']);

        // Return images as JSON
        return response()->json(['images' => $images]);
    }
}
