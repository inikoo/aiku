<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 May 2024 10:16:58 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateJobPositionsShare;
use App\Actions\HumanResources\JobPosition\Hydrators\JobPositionHydrateEmployees;
use App\Actions\SysAdmin\User\SyncRolesFromJobPositions;
use App\Models\HumanResources\Employee;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Organisation;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncEmployeeOtherOrganisationJobPositions
{
    use AsAction;

    public function handle(Employee $employee, Organisation $otherOrganisation, array $jobPositions): void
    {
        $jobPositionsIds = array_keys($jobPositions);

        $currentJobPositions = $employee->otherOrganisationJobPositions()->pluck('job_positions.id')->all();

        $newJobPositionsIds = array_diff($jobPositionsIds, $currentJobPositions);
        $removeJobPositions = array_diff($currentJobPositions, $jobPositionsIds);

        $employee->otherOrganisationJobPositions()->detach($removeJobPositions);

        foreach ($newJobPositionsIds as $jobPositionId) {
            $employee->otherOrganisationJobPositions()->attach(
                [
                    $jobPositionId => [
                        'group_id'              => $employee->group_id,
                        'organisation_id'       => $employee->organisation_id,
                        'other_organisation_id' => $otherOrganisation->id,
                        'scopes'                => $jobPositions[$jobPositionId]
                    ]
                ],
            );
        }

        if (count($newJobPositionsIds) || count($removeJobPositions)) {
            if ($employee->user) {
                SyncRolesFromJobPositions::run($employee->user);
            }


            // EmployeeHydrateJobPositionsShare::run($employee);

            foreach ($removeJobPositions as $jobPositionId) {
                $jobPosition = JobPosition::find($jobPositionId);
                // JobPositionHydrateEmployees::dispatch($jobPosition);
            }

            foreach ($newJobPositionsIds as $jobPositionId) {
                $jobPosition = JobPosition::find($jobPositionId);
                // JobPositionHydrateEmployees::dispatch($jobPosition);
            }
        }
    }
}
