<?php


namespace App\Jobs;

use App\Events\ImageGenerated;
use App\Events\ImageGenerationProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateMockImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $count;

    public function __construct($count)
    {
        $this->count = $count;
    }

    public function handle()
    {
        $mockedImages = [];
        $progress = 0;

        for ($i = 1; $i <= $this->count; $i++) {
            $mockedFilename = 'mocked_image_' . uniqid() . '.png';
            $imageUrl = "https://picsum.photos/200?random=".rand(1,10);

            $imageData = @file_get_contents($imageUrl);
            if ($imageData === false) {
                Log::error('Failed to fetch placeholder image', [
                    'url' => $imageUrl,
                    'attempt' => $i,
                    'user_id' => 2,
                ]);
                continue;
            }

            try {
                $mockedPath = 'images/mocked/' . $mockedFilename;
                Storage::disk('s3')->put($mockedPath, $imageData);

                $mockedS3Url = Storage::disk('s3')->url($mockedPath);

                $progress = ($i / $this->count) * 100;

                $imageFileData = [
                    'filename' => $mockedFilename,
                    'user_id' => 2,
                    'url' => $mockedS3Url,
                    'mime_type' => 'image/png',
                ];

                $mockedImages[] = $imageFileData;

                $imageFileData['progress'] = $progress;
                event(new ImageGenerationProgress($imageFileData));
                sleep(5);

            } catch (\Exception $e) {
                Log::error('Error storing mocked image', [
                    'filename' => $mockedFilename,
                    'error' => $e->getMessage(),
                    'user_id' => 2,
                ]);
            }

        }

        if (!empty($mockedImages)) {
            \App\Models\Image::insert($mockedImages);
            // Broadcast real-time updates


        } else {
            Log::warning('No images were generated successfully', [
                'user_id' => 2,
            ]);
        }
    }
}
