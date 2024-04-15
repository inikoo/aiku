<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 20 Jan 2024 16:48:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Organisation;

use App\Actions\HumanResources\JobPosition\StoreJobPosition;
use App\Actions\HumanResources\JobPosition\UpdateJobPosition;
use App\Models\SysAdmin\Organisation;
use App\Models\SysAdmin\Role;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedOrganisationJobPositions extends Seeder
{
    use AsAction;

    public function handle(Organisation $organisation): void
    {
        $jobPositions = collect(config("blueprint.job_positions.positions"));


        foreach ($jobPositions as $jobPositionData) {

            if (in_array($organisation->type, $jobPositionData['organisation_types'])) {
                $jobPosition = $organisation->josPositions()->where('code', $jobPositionData['code'])->first();
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
                    $jobPosition = StoreJobPosition::make()->action(
                        $organisation,
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

    public string $commandSignature = 'org:seed-job-positions {organisation?}';

    public function asCommand(Command $command): int
    {

        if($command->argument('organisation')) {
            $organisation = Organisation::where('slug', $command->argument('organisation'))->first();
            if(!$organisation) {
                $command->error("Organisation not found");
                return 1;
            }
            $this->handle($organisation);
            return 0;
        } else {
            foreach (Organisation::all() as $organisation) {
                $command->info("Seeding job positions for organisation: $organisation->name");
                $this->handle($organisation);
            }
        }



        return 0;
    }
}
