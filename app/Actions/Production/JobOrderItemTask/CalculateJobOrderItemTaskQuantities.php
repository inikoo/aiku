<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Aug 2026 13:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\JobOrderItemTask;

use App\Enums\Production\JobOrderItemTask\JobOrderItemTaskStateEnum;
use App\Enums\Production\ManufactureTaskSession\ManufactureTaskSessionStateEnum;
use App\Models\Production\JobOrderItemTask;
use App\Models\Production\ManufactureTaskSession;
use App\Models\Production\ManufactureTaskSessionShare;
use Lorisleiva\Actions\Concerns\AsAction;

class CalculateJobOrderItemTaskQuantities
{
    use AsAction;

    public function handle(JobOrderItemTask $jobOrderItemTask): JobOrderItemTask
    {
        $ownSessions = $jobOrderItemTask->sessions()
            ->where('state', ManufactureTaskSessionStateEnum::CLOSED)
            ->where('is_combined', false);
        $shares      = ManufactureTaskSessionShare::where('job_order_item_task_id', $jobOrderItemTask->id)
            ->whereHas('session', fn ($query) => $query->where('state', ManufactureTaskSessionStateEnum::CLOSED));

        $quantityMade     = (float) (clone $ownSessions)->sum('quantity_made') + (float) (clone $shares)->sum('quantity_made');
        $quantityRejected = (float) $ownSessions->sum('quantity_rejected') + (float) $shares->sum('quantity_rejected');

        $state = JobOrderItemTaskStateEnum::TODO;
        if (round($quantityMade, 3) >= round((float) $jobOrderItemTask->quantity_required, 3)) {
            $state = JobOrderItemTaskStateEnum::DONE;
        } elseif ($this->hasBeenWorked($jobOrderItemTask)) {
            $state = JobOrderItemTaskStateEnum::IN_PROGRESS;
        }

        $jobOrderItemTask->update([
            'quantity_made'     => $quantityMade,
            'quantity_rejected' => $quantityRejected,
            'state'             => $state,
        ]);

        return $jobOrderItemTask;
    }

    private function hasBeenWorked(JobOrderItemTask $jobOrderItemTask): bool
    {
        $isWorked = fn ($query) => $query->where('state', ManufactureTaskSessionStateEnum::OPEN)
            ->orWhere(fn ($closed) => $closed->where('state', ManufactureTaskSessionStateEnum::CLOSED)->where('quantity_made', '>', 0));

        if ($jobOrderItemTask->sessions()->where('is_combined', false)->where($isWorked)->exists()) {
            return true;
        }

        if (ManufactureTaskSessionShare::where('job_order_item_task_id', $jobOrderItemTask->id)
            ->whereHas('session', fn ($query) => $query->where('state', ManufactureTaskSessionStateEnum::OPEN))
            ->orWhere(fn ($query) => $query->where('job_order_item_task_id', $jobOrderItemTask->id)->where('quantity_made', '>', 0)
                ->whereHas('session', fn ($session) => $session->where('state', ManufactureTaskSessionStateEnum::CLOSED)))
            ->exists()) {
            return true;
        }

        return ManufactureTaskSession::whereIn('job_order_item_task_id', $jobOrderItemTask->combinedGroup()->pluck('id'))
            ->where('is_combined', true)
            ->where('state', ManufactureTaskSessionStateEnum::OPEN)
            ->exists();
    }
}
