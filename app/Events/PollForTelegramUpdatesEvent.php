<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use WeStacks\TeleBot\Objects\Update;

class PollForTelegramUpdatesEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The returned Update object
     */
    public $pollUpdate;

    /**
     * Create a new event instance.
     *
     * @param Update $update
     */
    public function __construct(Update $update)
    {
        $this->pollUpdate = $update;
    }

//    /**
//     * Get the channels the event should broadcast on.
//     *
//     * @return \Illuminate\Broadcasting\Channel|array
//     */
//    public function broadcastOn()
//    {
//        return new PrivateChannel('telegram');
//    }
}
