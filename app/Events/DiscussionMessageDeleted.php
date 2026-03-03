<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DiscussionMessageDeleted implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public function __construct(
    public int $assignmentId,
    public int $messageId,
  ) {
  }

  public function broadcastOn(): array
  {
    return [
      new PrivateChannel("assignment.{$this->assignmentId}.discussion"),
    ];
  }
}
