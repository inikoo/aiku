<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 09 Aug 2024 11:33:17 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition;

use App\Actions\HumanResources\JobPosition\Hydrators\JobPositionHydrateGuests;
use App\Actions\SysAdmin\User\SyncRolesFromJobPositions;
use App\Models\HumanResources\JobPosition;
use App\Models\SysAdmin\Guest;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncGuestJobPositions
{
    use AsAction;

    public function handle(Guest $guest, array $jobPositions): void
    {

        $jobPositionsIds = array_keys($jobPositions);

        $currentJobPositions = $guest->jobPositions()->pluck('job_positions.id')->all();
        $newJobPositionsIds  = array_diff($jobPositionsIds, $currentJobPositions);
        $removeJobPositions  = array_diff($currentJobPositions, $jobPositionsIds);

        $guest->jobPositions()->detach($removeJobPositions);


        foreach ($newJobPositionsIds as $jobPositionId) {



            $guest->jobPositions()->attach(
                [
                    $jobPositionId => [
                        'group_id' => $guest->group_id,
                        'scopes'   => $jobPositions[$jobPositionId]
                    ]
                ],
            );
        }


        if (count($newJobPositionsIds) || count($removeJobPositions)) {
            if ($guest->user) {
                SyncRolesFromJobPositions::run($guest->user);
            }

            foreach ($removeJobPositions as $jobPositionId) {
                $jobPosition=JobPosition::find($jobPositionId);
                JobPositionHydrateGuests::dispatch($jobPosition);
            }

            foreach ($newJobPositionsIds as $jobPositionId) {
                $jobPosition=JobPosition::find($jobPositionId);
                JobPositionHydrateGuests::dispatch($jobPosition);
            }


        }
    }
}
