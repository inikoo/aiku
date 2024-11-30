<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 07 May 2024 22:59:36 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Production\Production\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Production\RawMaterial\RawMaterialStateEnum;
use App\Enums\Production\RawMaterial\RawMaterialStockStatusEnum;
use App\Enums\Production\RawMaterial\RawMaterialTypeEnum;
use App\Enums\Production\RawMaterial\RawMaterialUnitEnum;
use App\Models\Production\Production;
use App\Models\Production\RawMaterial;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class ProductionHydrateRawMaterials
{
    use AsAction;
    use WithEnumStats;

    private Production $production;

    public function __construct(Production $production)
    {
        $this->production = $production;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->production->id))->dontRelease()];
    }


    public function handle(Production $production): void
    {
        $stats = [
            'number_raw_materials' => $production->rawMaterials()->count()
        ];


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'raw_materials',
                field: 'type',
                enum: RawMaterialTypeEnum::class,
                models: RawMaterial::class,
                where: function ($q) use ($production) {
                    $q->where('production_id', $production->id);
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
                where: function ($q) use ($production) {
                    $q->where('production_id', $production->id);
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
                where: function ($q) use ($production) {
                    $q->where('production_id', $production->id);
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
                where: function ($q) use ($production) {
                    $q->where('production_id', $production->id);
                }
            )
        );

        $production->stats()->update($stats);
    }
}
