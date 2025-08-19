<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class NewChatMessage implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;

    public $message;

    public function __construct(ChatMessage $message)
    {
        $this->message = $message->load('user');
    }

    public function broadcastOn()
    {
        return new PresenceChannel('chat.' . $this->message->room_id);
    }

    public function broadcastWith()
    {
        return ['message' => $this->message];
    }
}



