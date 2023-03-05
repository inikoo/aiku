<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 14:20:21 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace Database\Seeders;

use App\Models\SysAdmin\Role;
use App\Models\HumanResources\JobPosition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class JobPositionSeeder extends Seeder
{
    public function run()
    {
        $jobPositions = collect(config("blueprint.job_positions.positions"));


        foreach ($jobPositions as $jobPositionData) {
            JobPosition::upsert(
                [
                                    [
                                        'slug'       => $jobPositionData['slug'],
                                        'name'       => $jobPositionData['name'],
                                        'department' => Arr::get($jobPositionData, 'department'),
                                        'team'       => Arr::get($jobPositionData, 'team'),
                                        'data'       => '{}',
                                        'roles'      => '{}'
                                    ],
                                ],
                ['slug'],
                ['name']
            );


            $jobPosition = JobPosition::firstWhere('slug', $jobPositionData['slug']);
            $roles       = [];
            foreach ($jobPositionData['roles'] as $roleName) {
                if ($role = (new Role())->where('name', $roleName)->first()) {
                    $roles[] = $role->id;
                }
            }

            $jobPosition->update(
                [
                    'roles' => $roles
                ]
            );
        }
    }
}
