<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 12 Sept 2022 19:55:12 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Hydrators;

use App\Actions\Traits\WithNormalise;
use App\Models\HumanResources\JobPosition;
use App\Models\Organisations\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class HydrateOrganisation extends HydrateModel
{

    use WithNormalise;

    public string $commandSignature = 'hydrate:organisation {organisation_code?}';


    public function handle(Organisation $organisation): void
    {
        foreach ($organisation->jobPositions as $jobPosition) {
            $this->updateJobPositionsStats($organisation, $jobPosition);
        }

        $this->updateShareWorkTime($organisation);
    }


    public function updateJobPositionsStats(Organisation $organisation, JobPosition $jobPosition)
    {
        $organisation->jobPositions()->updateExistingPivot(
            $jobPosition->id,
            [
                'number_employees' => DB::table('employee_job_position')->where('job_position_id', $jobPosition->id)->count(),
                'number_work_time' => DB::table('employee_job_position')->where('job_position_id', $jobPosition->id)->sum('share'),
            ]
        );
    }

    public function updateShareWorkTime(Organisation $organisation)
    {
        $share = [];
        foreach ($organisation->jobPositions as $jobPosition) {
            $share[$jobPosition->id] = $jobPosition->pivot->number_work_time;
        }
        foreach ($this->normalise(collect($share)) as $id => $share) {
            $organisation->jobPositions()->updateExistingPivot(
                $id,
                [
                    'share_work_time' => $share
                ]
            );
        }
    }


    protected function getAllModels(): Collection
    {
        return Organisation::get();
    }

    public function asCommand(Command $command): void
    {
        $organisation = Organisation::where('code', $command->argument('organisation_code'))->first();
        if (!$organisation) {
            $command->error('Organisation not found');

            return;
        }

        if ($command->argument('organisation_code')) {
            $organisation = Organisation::where('code', $command->argument('organisation_code'))->first();
            if ($organisation) {
                $this->handle($organisation);
                $command->info('Done!');
            }
        } else {
            $this->loopAll($command);
        }
    }

}


