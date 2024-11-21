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

        foreach ($group->shops()->get() as $shop) {
            $this->seedShopAikuScopedSection($shop);
        }
        foreach ($group->organisations()->get() as $organisation) {
            $this->seedOrganisationAikuScopedSection($organisation);
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
        //... Warehouse, Fulfillment ... etc
    }


    public function seedGroupAikuScopedSection(Group $group): void
    {
        foreach (AikuSectionEnum::cases() as $case) {
            if ($case->scopeType() == 'Group') {
                $aikuSection = AikuSection::where('code', $case->value)->first();
                $code = $aikuSection->slug . '-grp-' . $group->slug;
                $name= $case->labels()[$case->value].' '.$group->code;

                $aikuScopedSection = AikuScopedSection::where('code', $code)->first();

                if($aikuScopedSection->exists()){
                    UpdateAikuScopedSection::make()->action($aikuScopedSection, [
                        'name' => $name
                    ]);
                } else {
                    StoreAikuScopedSection::make()->action($group, $aikuSection, [
                        'code' => $code,
                        'name' => $name
                    ]);
                }
            }
        }
    }

    public function seedShopAikuScopedSection(Shop $shop): void
    {
        foreach (AikuSectionEnum::cases() as $case) {
            if ($case->scopeType() == 'Shop') {

                $aikuSection = AikuSection::where('code', $case->value)->first();
                $code = $aikuSection->slug . '-shop-' . $shop->slug;
                $name= $case->labels()[$case->value].' '.$shop->code;

                $aikuScopedSection = AikuScopedSection::where('code', $code)->first();

                if($aikuScopedSection->exists()){
                    UpdateAikuScopedSection::make()->action($aikuScopedSection, [
                        'name' => $name
                    ]);
                } else {
                    StoreAikuScopedSection::make()->action($shop, $aikuSection, [
                        'code' => $code,
                        'name' => $name
                    ]);
                }
            }
        }
    }

    public function seedOrganisationAikuScopedSection(Organisation $organisation): void
    {
        foreach (AikuSectionEnum::cases() as $case) {
            if ($case->scopeType() == 'Organisation') {

                $aikuSection = AikuSection::where('code', $case->value)->first();
                $code = $aikuSection->slug . '-org-' . $organisation->slug;
                $name= $case->labels()[$case->value].' '.$organisation->code;

                $aikuScopedSection = AikuScopedSection::where('code', $code)->first();

                if($aikuScopedSection->exists()){
                    UpdateAikuScopedSection::make()->action($aikuScopedSection, [
                        'name' => $name
                    ]);
                } else {
                    StoreAikuScopedSection::make()->action($organisation, $aikuSection, [
                        'code' => $code,
                        'name' => $name
                    ]);
                }
            }
        }
    }

    public function seedFulfilmentAikuScopedSection(Fulfilment $fulfilment): void
    {
        foreach (AikuSectionEnum::cases() as $case) {
            if ($case->scopeType() == 'Fulfilment') {

                $aikuSection = AikuSection::where('code', $case->value)->first();
                $code = $aikuSection->slug . '-ful-' . $fulfilment->slug;
                $name= $case->labels()[$case->value].' '.$fulfilment->slug;

                $aikuScopedSection = AikuScopedSection::where('code', $code)->first();

                if($aikuScopedSection->exists()){
                    UpdateAikuScopedSection::make()->action($aikuScopedSection, [
                        'name' => $name
                    ]);
                } else {
                    StoreAikuScopedSection::make()->action($fulfilment, $aikuSection, [
                        'code' => $code,
                        'name' => $name
                    ]);
                }
            }
        }
    }

    public function seedProductionAikuScopedSection(Production $production): void
    {
        foreach (AikuSectionEnum::cases() as $case) {
            if ($case->scopeType() == 'Production') {

                $aikuSection = AikuSection::where('code', $case->value)->first();
                $code = $aikuSection->slug . '-prod-' . $production->slug;
                $name= $case->labels()[$case->value].' '.$production->code;

                $aikuScopedSection = AikuScopedSection::where('code', $code)->first();

                if($aikuScopedSection->exists()){
                    UpdateAikuScopedSection::make()->action($aikuScopedSection, [
                        'name' => $name
                    ]);
                } else {
                    StoreAikuScopedSection::make()->action($production, $aikuSection, [
                        'code' => $code,
                        'name' => $name
                    ]);
                }
            }
        }
    }

    public function seedWarehouseAikuScopedSection(Warehouse $warehouse): void
    {
        foreach (AikuSectionEnum::cases() as $case) {
            if ($case->scopeType() == 'Warehouse') {

                $aikuSection = AikuSection::where('code', $case->value)->first();
                $code = $aikuSection->slug . '-ware-' . $warehouse->slug;
                $name= $case->labels()[$case->value].' '.$warehouse->code;

                $aikuScopedSection = AikuScopedSection::where('code', $code)->first();

                if($aikuScopedSection->exists()){
                    UpdateAikuScopedSection::make()->action($aikuScopedSection, [
                        'name' => $name
                    ]);
                } else {
                    StoreAikuScopedSection::make()->action($warehouse, $aikuSection, [
                        'code' => $code,
                        'name' => $name
                    ]);
                }
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
