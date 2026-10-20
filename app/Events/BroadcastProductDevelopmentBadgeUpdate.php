<?php

/*
 * Author Louis Perez
 * Created on 20-10-2026-18h-30m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

/**
 * Refreshes one person's Developments badge on the rail, e.g. so it starts pulsing when they are reminded.
 */
class BroadcastProductDevelopmentBadgeUpdate implements ShouldBroadcastNow, ShouldDispatchAfterCommit
{
    use Dispatchable;
    use InteractsWithSockets;

    /**
     * @param array{waiting: int, late: int, reminded: bool}|null $developmentBadges
     */
    public function __construct(public int $userId, public ?array $developmentBadges)
    {
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('grp.personal.'.$this->userId)];
    }

    public function broadcastAs(): string
    {
        return 'development-badges-update';
    }

    public function broadcastWith(): array
    {
        return ['development_badges' => $this->developmentBadges];
    }
}
