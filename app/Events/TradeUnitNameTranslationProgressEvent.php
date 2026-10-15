<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Events;

use App\Models\Goods\TradeUnit;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TradeUnitNameTranslationProgressEvent implements ShouldBroadcastNow
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /** @param array{state: string, done: int, total: int, failed: array<int, string>} $progress */
    public function __construct(
        public TradeUnit $tradeUnit,
        public array $progress
    ) {
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('grp.trade-unit.'.$this->tradeUnit->id)
        ];
    }

    public function broadcastWith(): array
    {
        return $this->progress;
    }

    public function broadcastAs(): string
    {
        return 'name-translation-progress';
    }
}
