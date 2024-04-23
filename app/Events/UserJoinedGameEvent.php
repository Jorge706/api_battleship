<?php

namespace App\Events;


use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class UserJoinedGameEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $game;

    public function __construct($game)
    {
        // $this->user = $user;
        $this->game = $game;
    }

    public function broadcastOn()
    {
        return ['join'. $this->game]; //es como si pusiera join
    }

    public function broadcastAs()
    {
        return 'my-event';
    }
}
