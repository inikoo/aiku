<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:15:10 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

 namespace App\Actions\Manufacturing\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Manufacturing\Production\ProductionStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStateEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialStockStatusEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Manufacturing\RawMaterial\RawMaterialUnitEnum;
use App\Models\SysAdmin\Organisation;
use App\Models\Manufacturing\Production;
use App\Models\Manufacturing\RawMaterial;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrganisationHydrateManufacture
{
    use AsAction;
    use WithEnumStats;

    private Organisation $organisation;

    public function __construct(Organisation $organisation)
    {
        $this->organisation = $organisation;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->organisation->id))->dontRelease()];
    }


    public function handle(Organisation $organisation): void
    {
        $stats = [
            'number_productions' => $organisation->productions()->count(),
            'number_raw_materials' => $organisation->rawMaterials()->count()
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'productions',
                field: 'state',
                enum: ProductionStateEnum::class,
                models: Production::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'raw_materials',
                field: 'type',
                enum: RawMaterialTypeEnum::class,
                models: RawMaterial::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'raw_materials',
                field: 'state',
                enum: RawMaterialStateEnum::class,
                models: RawMaterial::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'raw_materials',
                field: 'unit',
                enum: RawMaterialUnitEnum::class,
                models: RawMaterial::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'raw_materials',
                field: 'stock_status',
                enum: RawMaterialStockStatusEnum::class,
                models: RawMaterial::class,
                where: function ($q) use ($organisation) {
                    $q->where('organisation_id', $organisation->id);
                }
            )
        );

        $organisation->manufactureStats()->update($stats);
    }
}
