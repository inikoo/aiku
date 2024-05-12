<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 20 Jan 2024 16:48:57 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group;

use App\Actions\SysAdmin\GroupJobPosition\StoreGroupJobPosition;
use App\Actions\SysAdmin\GroupJobPosition\UpdateGroupJobPosition;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\GroupJobPosition;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedGroupJobPositions extends Seeder
{
    use AsAction;

    public function handle(Group $group): void
    {
        $groupJobPositions = collect(config("blueprint.job_positions.positions"));


        foreach ($groupJobPositions as $groupJobPositionData) {
            /** @var GroupJobPosition $groupJobPosition */
            $groupJobPosition = $group->groupJobPositions()->where('code', $groupJobPositionData['code'])->first();
            if ($groupJobPosition) {
                UpdateGroupJobPosition::make()->action(
                    $groupJobPosition,
                    [
                        'name'       => $groupJobPositionData['name'],
                        'department' => Arr::get($groupJobPositionData, 'department'),
                        'team'       => Arr::get($groupJobPositionData, 'team'),
                        'scope'      => Arr::get($groupJobPositionData, 'scope')
                    ]
                );
            } else {
                StoreGroupJobPosition::make()->action(
                    $group,
                    [
                        'code'       => $groupJobPositionData['code'],
                        'name'       => $groupJobPositionData['name'],
                        'department' => Arr::get($groupJobPositionData, 'department'),
                        'team'       => Arr::get($groupJobPositionData, 'team'),
                        'scope'      => Arr::get($groupJobPositionData, 'scope')
                    ],
                );
            }
        }
    }


    public string $commandSignature = 'group:seed-group-job-positions {group?}';

    public function asCommand(Command $command): int
    {
        if ($command->argument('group')) {
            $group = Group::where('slug', $command->argument('group'))->first();
            if (!$group) {
                $command->error("Group not found");
                return 1;
            }
            $this->handle($group);

            return 0;
        } else {
            foreach (Group::all() as $group) {
                $command->info("Seeding job positions for group: $group->name");
                $this->handle($group);
            }
        }


        return 0;
    }
}
