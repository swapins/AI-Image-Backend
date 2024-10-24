<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ImageGenerationProgress implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $imageData;

    public function __construct($imageData)
    {
        $this->imageData = $imageData;
        // Log the image data and broadcast event initialization
        Log::debug('ImageGenerationProgress event initialized', [
            'imageData' => $imageData
        ]);
    }

    public function broadcastOn()
    {
        Log::debug('Broadcasting on channel: images.' . $this->imageData['user_id']);
        return new Channel('images.' . $this->imageData['user_id']);

    }

    public function broadcastAs()
    {
        return 'image.generated';
    }
}
