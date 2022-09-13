<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 13 Sept 2022 01:26:49 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Hydrators;

use App\Actions\Traits\WithNormalise;
use App\Models\HumanResources\Employee;
use Illuminate\Support\Collection;


class HydrateEmployee extends HydrateModel
{

    use WithNormalise;

    public string $commandSignature = 'hydrate:employee {organisation_code} {id?}';


    public function handle(Employee $employee): void
    {
        $this->updateJobPositionsShare($employee);
    }

    public function updateJobPositionsShare(Employee $employee): void
    {
        foreach ($this->getJobPositionShares($employee) as $job_position_id => $share) {
            $employee->jobPositions()->updateExistingPivot($job_position_id, [
                'share' => $share,
            ]);
        }
    }


    protected function getModel(int $id): Employee
    {
        return Employee::findOrFail($id);
    }

    protected function getAllModels(): Collection
    {
        return Employee::get();
    }

    function getJobPositionShares(Employee $employee): array
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

}


