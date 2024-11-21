<?php
/*
 * author Arya Permana - Kirin
 * created on 21-11-2024-11h-16m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\SysAdmin\Group;

use App\Actions\Analytics\AikuScopedSection\StoreAikuScopedSection;
use App\Actions\Analytics\AikuScopedSection\UpdateAikuScopedSection;
use App\Actions\GrpAction;
use App\Actions\Traits\WithAttachMediaToModel;
use App\Enums\Analytics\AikuSection\AikuSectionEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Models\Analytics\AikuScopedSection;
use App\Models\Analytics\AikuSection;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Inventory\Warehouse;
use App\Models\Manufacturing\Production;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsAction;

class SeedAikuScopedSections extends GrpAction
{
    use AsAction;
    use WithAttachMediaToModel;

    public function handle(Group $group): void
    {
        $this->seedGroupAikuScopedSection($group);

        foreach ($group->organisations()->get() as $organisation) {
            $this->seedOrganisationAikuScopedSection($organisation);
        }
        /** @var Shop $shop */
        foreach ($group->shops()->get() as $shop) {
            if ($shop->type != ShopTypeEnum::FULFILMENT) {
                $this->seedShopAikuScopedSection($shop);
            }
        }

        foreach ($group->fulfilments()->get() as $fulfilment) {
            $this->seedFulfilmentAikuScopedSection($fulfilment);
        }
        foreach ($group->productions()->get() as $production) {
            $this->seedProductionAikuScopedSection($production);
        }
        foreach ($group->warehouses()->get() as $warehouse) {
            $this->seedWarehouseAikuScopedSection($warehouse);
        }

    }


    public function seedAikuScopedSection(Group|Shop|Organisation|Fulfilment|Production|Warehouse $model): void
    {
        foreach (AikuSectionEnum::cases() as $case) {
            if ($case->scopeType() == class_basename($model)) {
                $aikuSection = AikuSection::where('code', $case->value)->first();
                $code        = $aikuSection->slug;
                $name        = $case->labels()[$case->value].' '.$model->code;

                $aikuScopedSection = AikuScopedSection::where('code', $code)->first();

                if ($aikuScopedSection) {
                    UpdateAikuScopedSection::make()->action($aikuScopedSection, [
                        'name' => $name
                    ]);
                } else {
                    StoreAikuScopedSection::make()->action($model, $aikuSection, [
                        'code' => $code,
                        'name' => $name
                    ]);
                }
            }
        }
    }

    public function seedGroupAikuScopedSection(Group $group): void
    {
        $this->seedAikuScopedSection($group);
    }

    public function seedShopAikuScopedSection(Shop $shop): void
    {
        $this->seedAikuScopedSection($shop);
    }

    public function seedOrganisationAikuScopedSection(Organisation $organisation): void
    {
        $this->seedAikuScopedSection($organisation);
    }

    public function seedFulfilmentAikuScopedSection(Fulfilment $fulfilment): void
    {
        $this->seedAikuScopedSection($fulfilment);
    }

    public function seedProductionAikuScopedSection(Production $production): void
    {
        $this->seedAikuScopedSection($production);
    }

    public function seedWarehouseAikuScopedSection(Warehouse $warehouse): void
    {
        $this->seedAikuScopedSection($warehouse);
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
