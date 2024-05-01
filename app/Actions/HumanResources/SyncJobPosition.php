<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 21 Sep 2023 11:34:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources;

use App\Actions\HumanResources\Employee\Hydrators\EmployeeHydrateJobPositionsShare;
use App\Actions\HumanResources\JobPosition\HydrateJobPosition;
use App\Models\HumanResources\Employee;
use App\Models\SysAdmin\Guest;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncJobPosition
{
    use AsAction;

    public function handle(Employee|Guest $model, array $jobPositions): void
    {

        $currentJobPositions=$model->jobPositions()->pluck('job_positions.id')->all();

        $newJobPositions   =array_diff($jobPositions, $currentJobPositions);
        $removeJobPositions=array_diff($currentJobPositions, $jobPositions);

        $model->jobPositions()->detach($removeJobPositions);

        $model->jobPositions()->attach($newJobPositions);


        if(count($newJobPositions) || count($removeJobPositions)) {
            if($user=$model->user) {

                $roles=[];
                foreach($model->jobPositions as $jobPosition) {
                    $roles=array_merge($roles, $jobPosition->roles()->pluck('id')->all());
                }


                $user->roles()->sync($roles);
                $user->refresh();
            }


            if(class_basename($model)=='Employee') {
                EmployeeHydrateJobPositionsShare::dispatch($model);
                foreach($jobPositions as $jobPositionId) {
                    HydrateJobPosition::dispatch($jobPositionId);
                }
            }
        }




    }
}
