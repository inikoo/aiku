<?php
/*
 * author Arya Permana - Kirin
 * created on 21-11-2024-11h-16m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\SysAdmin\Group;

use App\Actions\GrpAction;
use App\Actions\Traits\WithAttachMediaToModel;
use App\Enums\Analytics\AikuSection\AikuSectionEnum;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedAikuScopedSections extends GrpAction
{
    use AsAction;
    use WithAttachMediaToModel;

    public function handle(Group $group): void
    {
        $this->seedGroupAikuScopedSection($group);

        foreach ($group->shops()->get() as $shop) {
            $this->seedShopAikuScopedSection($shop);

        }
        //... Warehouse, Fulfillment ... etc
    }


    public function seedGroupAikuScopedSection(Group $group): void
    {
        foreach (AikuSectionEnum::cases() as $case) {
            if ($case->scopeType() == 'Group') {
                $name= $case->labels()[$case->value].' '.$group->code;
                // if exist  call Update  (name) of not
                //call StoreAikuScopedSection::
            }
        }
    }

    public function seedShopAikuScopedSection(Shop $shop): void
    {
        foreach (AikuSectionEnum::cases() as $case) {
            if ($case->scopeType() == 'Shop') {
                $name= $case->labels()[$case->value].' '.$shop->code;
                // if exist  call Update  (name)  of not
                //call StoreAikuScopedSection::
            }
        }
    }


    public string $commandSignature = 'group:seed_aiku_scoped_sections';

    public function asCommand(Command $command): int
    {
        foreach (Group::all() as $group) {
            $command->info("Seeding aiku sections types for group: $group->name");
            $this->handle($group);
        }

        return 0;
    }
}
