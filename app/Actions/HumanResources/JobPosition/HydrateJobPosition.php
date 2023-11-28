<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 21 Sept 2022 16:16:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\JobPosition;

use App\Actions\Traits\WithNormalise;
use App\Models\HumanResources\JobPosition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class HydrateJobPosition
{
    use AsAction;
    use WithNormalise;


    public function handle(int $jobPositionID): void
    {
        $jobPosition=JobPosition::find($jobPositionID);
        $jobPosition->update(
            [
                'number_employees' => DB::table('job_positionables')->where('job_position_id', $jobPosition->id)->count(),
                'number_work_time' => DB::table('job_positionables')->where('job_position_id', $jobPosition->id)->sum('share'),
            ]
        );


        $this->updateNormalisedJobPositionsShare();
    }

    private function updateNormalisedJobPositionsShare(): void
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

    public string $commandSignature = 'hydrate:job-positions {job-positions?*}';

    public function asCommand(Command $command): int
    {

        if(!$command->argument('job-positions')) {
            $jobPositions=JobPosition::all();
        } else {
            $jobPositions =  JobPosition::query()
                ->when($command->argument('job-positions'), function ($query) use ($command) {
                    $query->whereIn('slug', $command->argument('job-positions'));
                })
                ->cursor();
        }


        $exitCode = 0;

        foreach ($jobPositions as $jobPosition) {

            $this->handle($jobPosition);
            $command->line("Jon position $jobPosition->name hydrated 💦");

        }

        return $exitCode;
    }



}
