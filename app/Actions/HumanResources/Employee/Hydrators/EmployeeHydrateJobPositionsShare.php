<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 11 Mar 2023 16:02:37 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\Hydrators;

use App\Actions\Traits\WithNormalise;
use App\Actions\Traits\WithTenantJob;
use App\Models\HumanResources\Employee;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class EmployeeHydrateJobPositionsShare implements ShouldBeUnique
{
    use AsAction;
    use WithTenantJob;
    use WithNormalise;

    public function handle(Employee $employee): void
    {
        foreach ($this->getJobPositionShares($employee) as $job_position_id => $share) {
            $employee->jobPositions()->updateExistingPivot($job_position_id, [
                'share' => $share,
            ]);
        }
    }

    public function getJobPositionShares(Employee $employee): array
    {
        $jobPositions = $this->normalise(
            collect(
                $employee->jobPositions()->whereNotNull('share')->pluck('share', 'job_position_id')
            )
        );


        $jobPositionsNoShare = $employee->jobPositions()->whereNull('share')->pluck('job_position_id');

        $numberJobPositionsNoShare = count($jobPositionsNoShare);
        $numberJobPositions        = count($jobPositions);

        if ($numberJobPositionsNoShare == 0) {
            return $jobPositions;
        }

        $numberSlots = $numberJobPositionsNoShare + $numberJobPositions;

        $shares = [];
        foreach ($jobPositionsNoShare as $id) {
            $shares[$id] = 1 / $numberSlots;
        }
        foreach ($jobPositions as $id => $share) {
            $shares[$id] = $share * $numberJobPositions / $numberSlots;
        }


        return $this->normalise(collect($shares));
    }

    public function getJobUniqueId(Employee $employee): int
    {
        return $employee->id;
    }
}
