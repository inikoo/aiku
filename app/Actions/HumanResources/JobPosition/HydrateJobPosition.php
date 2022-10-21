<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 16:16:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition;

use App\Actions\HydrateModel;
use App\Actions\Traits\WithNormalise;
use App\Models\HumanResources\JobPosition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class HydrateJobPosition extends HydrateModel
{

    use WithNormalise;

    public string $commandSignature = 'hydrate:job-positions {tenant_code?} {id?}';

    public function handle(JobPosition $jobPosition): void
    {
        $jobPosition->update(

            [
                'number_employees' => DB::table('employee_job_position')->where('job_position_id', $jobPosition->id)->count(),
                'number_work_time' => DB::table('employee_job_position')->where('job_position_id', $jobPosition->id)->sum('share'),
            ]
        );


        $this->updateNormalisedJobPositionsShare();
    }

    private function updateNormalisedJobPositionsShare()
    {
        foreach ($this->getNormalisedJobPositionsShare() as $id => $share) {
            JobPosition::find($id)->update(
                [
                    'share_work_time' => $share
                ]
            );
        }
    }

    private function getNormalisedJobPositionsShare(): array
    {
        $share = [];
        /** @var JobPosition $jobPosition */
        foreach (JobPosition::all() as $jobPosition) {
            $share[$jobPosition->id] = $jobPosition->number_work_time;
        }

        return $this->normalise(collect($share));
    }

    protected function getModel(int $id): JobPosition
    {
        return JobPosition::findOrFail($id);
    }

    protected function getAllModels(): Collection
    {
        return JobPosition::all();
    }


}


