<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 20 Jun 2024 17:50:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SysAdmin\Group;

use App\Actions\Web\WebBlockType\StoreWebBlockType;
use App\Actions\Web\WebBlockType\UpdateWebBlockType;
use App\Actions\Web\WebBlockTypeCategory\StoreWebBlockTypeCategory;
use App\Actions\Web\WebBlockTypeCategory\UpdateWebBlockTypeCategory;
use App\Models\SysAdmin\Group;
use App\Models\Web\WebBlockTypeCategory;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedWebBlockTypes
{
    use AsAction;

    public function handle(Group $group): void
    {
        foreach (Storage::disk('datasets')->files('web-block-type-categories') as $file) {
            $webBlockTypeCategoryData = json_decode(Storage::disk('datasets')->get($file), true);
            $webBlockTypeCategory     = WebBlockTypeCategory::where('slug', $webBlockTypeCategoryData['slug'])->first();
            if ($webBlockTypeCategory) {
                UpdateWebBlockTypeCategory::run($webBlockTypeCategory, Arr::except($webBlockTypeCategoryData, 'webBlockTypes'));
            } else {
                $webBlockTypeCategory = StoreWebBlockTypeCategory::run($group, Arr::except($webBlockTypeCategoryData, 'webBlockTypes'));
            }
            foreach (Arr::get($webBlockTypeCategoryData, 'webBlockTypes', []) as $webBlockTypeData) {
                data_set($webBlockTypeData, 'blueprint', Arr::get($webBlockTypeCategory, 'blueprint'), overwrite: false);

                $webBlockType = $webBlockTypeCategory->webBlockTypes()->where('code', Arr::get($webBlockTypeData, 'code'))->first();
                if ($webBlockType) {
                    UpdateWebBlockType::run($webBlockType, $webBlockTypeData);
                } else {
                    StoreWebBlockType::run($webBlockTypeCategory, $webBlockTypeData);
                }
            }
        }
    }

    public string $commandSignature = 'group:seed-web-block-types';

    public function asCommand(Command $command): int
    {
        foreach (Group::all() as $group) {
            $command->info("Seeding web block types/categories for group: $group->name");
            $this->handle($group);
        }

        return 0;
    }
}
