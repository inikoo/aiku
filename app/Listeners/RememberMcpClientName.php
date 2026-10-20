<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;
use Laravel\Mcp\Events\SessionInitialized;

/**
 * The name an assistant announces when it connects, kept for its session so what it saves can be
 * told apart per assistant. A client that never sends the session id back stays unnamed.
 */
class RememberMcpClientName
{
    public static function key(string $sessionId): string
    {
        return 'mcp:client:'.$sessionId;
    }

    public function handle(SessionInitialized $event): void
    {
        if ($event->clientName()) {
            Cache::put(self::key($event->sessionId), mb_substr($event->clientName(), 0, 100), now()->addDays(30));
        }
    }
}
