<?php

namespace App\Events;

use App\Notification;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class NotificationEvent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets;
    public $notification;
    public $payload;
    private $channel;

    /**
     * Create a new event instance.
     *
     * @param object $notification Eloquent Notifikasi Instance
     * @param object $payload Informasi tambahan yang akan diberikan
     * @param string $channel channel yang dituju
     * @return void
     */
    public function __construct($notification = null, $payload = null, $channel = null)
    {
        $this->notification = $notification;
        $this->payload = $payload;
        $this->channel = $channel;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel($this->channel);
    }

    public function broadcastWith()
    {
        return [
            'notification'  => $this->notification,
            'payload'       => $this->payload,
        ];
    }

    /**
     * @param Notification $notification Eloquent Notifikasi Instance
     */
    public function notification($notification)
    {
        $this->notification = $notification;
        return $this;
    }

    /**
     * @param object $payload Informasi tambahan yang akan diberikan
     */
    public function payload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @param string $channel channel yang dituju
     */
    public function channel($channel)
    {
        $this->channel = $channel;
        return $this;
    }
}
