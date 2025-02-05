<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PrivateEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    const CAMPAIGN_EVENT = 'campaign';
    const USER_EVENT = 'user';
    const DEFAULT_EVENT = 'notification';

    /**
     * Create a new event instance.
     */
    public function __construct(
        public string $message,
        public User $user,
        public array $data = [],
        public string $event = self::DEFAULT_EVENT
    ) {}

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("App.Models.User.{$this->user->id}"),
        ];
    }

    public function broadcastWith(): array
    {
        return array(
            'message' => $this->message,
            'user' => $this->user->id,
            'data' => $this->data,
        );
    }

    public function broadcastAs(): string
    {
        return 'private.' . $this->event;
    }
}
