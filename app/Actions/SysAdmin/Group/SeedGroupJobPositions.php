<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 14:20:21 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\SysAdmin\Group;

use App\Actions\HumanResources\JobPosition\StoreJobPosition;
use App\Actions\HumanResources\JobPosition\UpdateJobPosition;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Role;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedGroupJobPositions extends Seeder
{
    use AsAction;
    public function handle(Group $group): void
    {
        $jobPositions = collect(config("blueprint.job_positions.positions"));


        foreach ($jobPositions as $jobPositionData) {
            $jobPosition = $group->josPositions()->where('code', $jobPositionData['code'])->first();
            if ($jobPosition) {
                UpdateJobPosition::run(
                    $jobPosition,
                    [
                        'name'       => $jobPositionData['name'],
                        'department' => Arr::get($jobPositionData, 'department'),
                        'team'       => Arr::get($jobPositionData, 'team'),
                    ]
                );
            } else {
                $jobPosition= StoreJobPosition::make()->action(
                    $group,
                    [
                        'code'       => $jobPositionData['code'],
                        'name'       => $jobPositionData['name'],
                        'department' => Arr::get($jobPositionData, 'department'),
                        'team'       => Arr::get($jobPositionData, 'team'),
                    ],
                );
            }


            $roles = [];
            foreach ($jobPositionData['roles'] as $roleName) {
                if ($role = (new Role())->where('name', $roleName)->first()) {
                    $roles[] = $role->id;
                }
            }

            $jobPosition->roles()->sync($roles);

        }
    }

    public string $commandSignature = 'group:seed-job-positions';

    public function asCommand(Command $command): int
    {

        foreach (Group::all() as $group) {
            $command->info("Seeding job positions for group: $group->name");
            $this->handle($group);
        }
        return 0;
    }
}
