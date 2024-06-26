<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 19 Jun 2024 18:03:42 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group;

use App\Actions\GrpAction;
use App\Actions\Ordering\Platform\StorePlatform;
use App\Actions\Traits\WithAttachMediaToModel;
use App\Enums\Ordering\Platform\PlatformTypeEnum;
use App\Models\SysAdmin\Group;
use Exception;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedPlatforms extends GrpAction
{
    use AsAction;
    use WithAttachMediaToModel;

    public function handle(Group $group): void
    {

        foreach (PlatformTypeEnum::cases() as $case) {

            $code= $case->value;

            if($group->platforms()->where('code', $code)->exists()) {
                continue;
            }

            StorePlatform::make()->action(
                $group,
                [
                'code' => $code,
                'name' => $case->labels()[$case->value],
                'type' => $case
                ]
            );
        }
    }

    public string $commandSignature = 'group:seed-platforms {group : group slug}';

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
        echo "Success seed the platforms ✅ \n";

        return 0;
    }
}
