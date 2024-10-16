<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jun 2024 17:50:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group;

use App\Actions\Web\WebBlockType\StoreWebBlockType;
use App\Actions\Web\WebBlockType\UpdateWebBlockType;
use App\Models\SysAdmin\Group;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedWebBlockTypes
{
    use AsAction;

    public function handle(Group $group): void
    {
        foreach (Storage::disk('datasets')->files('web-block-types') as $file) {
            $webBlockTypeData = Arr::only(
                json_decode(Storage::disk('datasets')->get($file), true),
                [
                    'scope',
                    'code',
                    'name',
                    'fixed',
                    'blueprint'
                ]
            );

            $webBlockType = $group->webBlockTypes()->where('code', Arr::get($webBlockTypeData, 'code'))->first();
            if ($webBlockType) {
                UpdateWebBlockType::run($webBlockType, $webBlockTypeData);
            } else {
                StoreWebBlockType::run($group, $webBlockTypeData);
            }
        }
    }

    public string $commandSignature = 'group:seed-web-block-types';

    public function asCommand(Command $command): int
    {
        foreach (Group::all() as $group) {
            $command->info("Seeding web block types for group: $group->name");
            $this->handle($group);
        }

        return 0;
    }
}
