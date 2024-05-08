<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 22:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\ClockingMachine\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\HumanResources\Clocking\ClockingTypeEnum;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ClockingMachineHydrateClockings
{
    use AsAction;
    use WithEnumStats;

    private ClockingMachine $clockingMachine;

    public function __construct(ClockingMachine $clockingMachine)
    {
        $this->clockingMachine = $clockingMachine;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->clockingMachine->id))->dontRelease()];
    }

    public function handle(ClockingMachine $clockingMachine): void
    {
        $stats = [
            'number_clockings' => $clockingMachine->clockings()->count(),
            'last_clocking_at' => $clockingMachine->clockings()->max('clocked_at') ?? null
        ];

        $stats=array_merge($stats, $this->getEnumStats(
            model:'clockings',
            field: 'type',
            enum: ClockingTypeEnum::class,
            models: Clocking::class,
            where: function ($q) use ($clockingMachine) {
                $q->where('clocking_machine_id', $clockingMachine->id);
            }
        ));

        $clockingMachine->stats()->update($stats);
    }


}
