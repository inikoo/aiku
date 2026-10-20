<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 14 Sep 2026 14:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureTaskSession;

use App\Models\Production\ManufactureTaskSession;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculateManufactureTaskSessionBreakMinutes
{
    use AsAction;

    /**
     * Only the part of a break that overlaps the session window is deducted from its paid hours.
     */
    public function handle(ManufactureTaskSession $session, Carbon $until): ManufactureTaskSession
    {
        $session->update(['break_minutes' => (int) round($session->breakSecondsUntil($until) / 60)]);

        return $session;
    }
}
