<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 14:20:21 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace Database\Seeders;

use App\Actions\HumanResources\JobPosition\StoreJobPosition;
use App\Actions\HumanResources\JobPosition\UpdateJobPosition;
use App\Models\Auth\Role;
use App\Models\HumanResources\JobPosition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class JobPositionSeeder extends Seeder
{
    public function run(): void
    {
        $jobPositions = collect(config("blueprint.job_positions.positions"));


        foreach ($jobPositions as $jobPositionData) {
            $jobPosition = JobPosition::where('code', $jobPositionData['code'])->first();
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
                $jobPosition= StoreJobPosition::run(
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
}
