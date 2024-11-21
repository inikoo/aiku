<?php
/*
 * author Arya Permana - Kirin
 * created on 21-11-2024-11h-16m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\SysAdmin\Group;

use App\Actions\Analytics\AikuSection\StoreAikuSection;
use App\Actions\GrpAction;
use App\Actions\Ordering\Platform\StorePlatform;
use App\Actions\Traits\WithAttachMediaToModel;
use App\Enums\Analytics\SectionEnum;
use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Models\Analytics\AikuSection;
use App\Models\SysAdmin\Group;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedAikuSections extends GrpAction
{
    use AsAction;
    use WithAttachMediaToModel;

    public function handle(Group $group): AikuSection
    {
        foreach (SectionEnum::cases() as $case) {
            $name = $case->labels()[$case->value];
            if ($group->platforms()->where('name', $name)->exists()) {
                continue;
            }
    
            $storedSection = StoreAikuSection::make()->action(
                $group,
                [
                    'name' => $name,
                ]
            );

            $scopeType = $case->scopeType();
            match ($scopeType) {
                'Group' => $storedSection->groups()->attach($group),
                'Organisation' => $storedSection->organisations()->attach($group->organisations->pluck('id')->toArray()),
                'Shop' => $storedSection->shops()->attach($group->shops->pluck('id')->toArray()),
                'Fulfilment' => $storedSection->fulfilments()->attach($group->fulfilments->pluck('id')->toArray()),
                'Production' => $storedSection->productions()->attach($group->productions->pluck('id')->toArray()),
                'Warehouse' => $storedSection->warehouses()->attach($group->warehouses->pluck('id')->toArray()),
                default => throw new \Exception("Unknown scope type: {$scopeType}"),
            };
        }

        return $storedSection;
    }

    public string $commandSignature = 'group:seed-sections {group : group slug}';

    public function asCommand(Command $command): int
    {
        try {
            $group       = Group::where('slug', $command->argument('group'))->firstOrFail();
            $this->group = $group;
            app()->instance('group', $group);
            setPermissionsTeamId($group->id);
        } catch (Exception $e) {
            $command->error($e->getMessage());

            return 1;
        }

        $this->handle($group);
        echo "Success seed the sections ✅ \n";

        return 0;
    }
}
