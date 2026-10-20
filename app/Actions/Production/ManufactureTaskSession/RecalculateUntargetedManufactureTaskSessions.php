<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 17:30:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\ManufactureTaskSession;

use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionActivityTypeEnum;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum;
use App\Models\Production\Artefact;
use App\Models\Production\ManufactureTaskSession;
use Lorisleiva\Actions\Concerns\AsAction;

class RecalculateUntargetedManufactureTaskSessions
{
    use AsAction;

    /**
     * Sessions closed this pay week on a step that had no target yet were paid at band 0. Once the step gets
     * a target they are paid against it, as if it had been there all along. Earlier weeks are already in
     * an exported payroll and stay as they are.
     */
    public function handle(Artefact $artefact): int
    {
        $sessions = ManufactureTaskSession::query()
            ->where('state', ManufactureTaskSessionStateEnum::CLOSED)
            ->where('activity_type', ManufactureTaskSessionActivityTypeEnum::PRODUCTION)
            ->whereNull('standard_rate')
            ->where('ended_at', '>=', now()->startOfWeek())
            ->whereHas('jobOrderItemTask.jobOrderItem', fn ($query) => $query->where('artefact_id', $artefact->id))
            ->get();

        $recalculated = 0;
        foreach ($sessions as $session) {
            $standardRate = $session->recipeStandardRate();
            if ($standardRate === null) {
                continue;
            }

            $session->standard_rate   = $standardRate;
            $session->is_under_target = CloseManufactureTaskSession::make()->isUnderTarget($session);
            $session->save();

            CalculateManufactureTaskSessionPay::run($session);
            $recalculated++;
        }

        return $recalculated;
    }
}
