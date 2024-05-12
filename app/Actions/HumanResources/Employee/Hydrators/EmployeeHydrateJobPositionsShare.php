<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 16:02:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\Hydrators;

use App\Actions\Traits\WithJobPositionableShare;
use App\Actions\Traits\WithNormalise;

use App\Models\HumanResources\Employee;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class EmployeeHydrateJobPositionsShare
{
    use AsAction;
    use WithNormalise;
    use WithJobPositionableShare;

    private Employee $employee;

    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->employee->id))->dontRelease()];
    }

    public function handle(Employee $employee): void
    {

        $employee->stats()->update(
            [
                'number_job_positions' => $employee->jobPositions()->count(),
            ]
        );

        foreach ($this->getJobPositionShares($employee) as $job_position_id => $share) {
            $employee->jobPositions()->updateExistingPivot($job_position_id, [
                'share' => $share,
            ]);
        }
    }


}
