<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use App\Models\game;

class UserJoinedGameEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $user;
    public $game;
    /**
     * Create a new event instance.
     */
    public function __construct(User $user, game $game)
    {
        $this->user = $user;
        $this->game = $game;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('game.' . $this->game->id);
    }
}
