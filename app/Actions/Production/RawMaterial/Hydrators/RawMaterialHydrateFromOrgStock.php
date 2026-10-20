<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Aug 2026 17:00:00 Central European Summer Time, Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Production\RawMaterial\Hydrators;

use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateCurrentSupplierSkuCost;
use App\Enums\Inventory\OrgStock\OrgStockQuantityStatusEnum;
use App\Enums\Production\RawMaterial\RawMaterialStockStatusEnum;
use App\Enums\Production\RawMaterial\RawMaterialTypeEnum;
use App\Models\Production\Artefact;
use App\Models\Production\RawMaterial;
use App\Models\Production\RecipeStepRawMaterial;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Lorisleiva\Actions\Concerns\AsAction;

class RawMaterialHydrateFromOrgStock implements ShouldBeUnique
{
    use AsAction;

    public string $jobQueue = 'hydrators-slave';

    public function getJobUniqueId(RawMaterial $rawMaterial): string
    {
        return (string)$rawMaterial->id;
    }

    public function handle(RawMaterial $rawMaterial): RawMaterial
    {
        $orgStock = $rawMaterial->orgStock;
        if (!$orgStock) {
            return $rawMaterial;
        }

        $stockStatus = match ($orgStock->quantity_status) {
            OrgStockQuantityStatusEnum::EXCESS => RawMaterialStockStatusEnum::SURPLUS,
            OrgStockQuantityStatusEnum::LOW => RawMaterialStockStatusEnum::LOW,
            OrgStockQuantityStatusEnum::CRITICAL => RawMaterialStockStatusEnum::CRITICAL,
            OrgStockQuantityStatusEnum::OUT_OF_STOCK => RawMaterialStockStatusEnum::OUT_OF_STOCK,
            OrgStockQuantityStatusEnum::ERROR => RawMaterialStockStatusEnum::ERROR,
            default => RawMaterialStockStatusEnum::OPTIMAL,
        };

        $artefactId = $rawMaterial->type === RawMaterialTypeEnum::INTERMEDIATE
            ? Artefact::where('org_stock_id', $orgStock->id)->value('id')
            : null;

        $unitCost = $artefactId
            ? $this->recipeMaterialsCost($artefactId, $rawMaterial->id)
            : OrgStockHydrateCurrentSupplierSkuCost::make()->getSupplierUnitCost($orgStock);

        $previousUnitCost = round((float) $rawMaterial->unit_cost, 3);

        $rawMaterial->update([
            'quantity_on_location' => $orgStock->quantity_in_locations ?? 0,
            'stock_status'         => $stockStatus,
            'artefact_id'          => $artefactId ?? $rawMaterial->artefact_id,
            'unit_cost'            => $unitCost === null ? $rawMaterial->unit_cost : round($unitCost, 3),
        ]);

        if (round((float) $rawMaterial->unit_cost, 3) !== $previousUnitCost) {
            static::dispatchIntermediatesUsing($rawMaterial);
        }

        return $rawMaterial;
    }

    /**
     * An intermediate costs what its artefact's recipe materials cost per artefact unit, which is
     * one trade unit of the SKO, the same unit the raw material is consumed in. Null while that
     * recipe has no materials, so a cost set by hand is not wiped to zero.
     */
    public function recipeMaterialsCost(int $artefactId, int $exceptRawMaterialId): ?float
    {
        $recipe = RecipeStepRawMaterial::query()
            ->join('artefacts_manufacture_tasks', 'artefacts_manufacture_tasks.id', 'recipe_step_raw_materials.artefact_manufacture_task_id')
            ->join('raw_materials', 'raw_materials.id', 'recipe_step_raw_materials.raw_material_id')
            ->where('artefacts_manufacture_tasks.artefact_id', $artefactId)
            ->where('raw_materials.id', '!=', $exceptRawMaterialId)
            ->whereNull('raw_materials.deleted_at')
            ->selectRaw('count(*) as number_lines, sum(recipe_step_raw_materials.quantity_per_unit * raw_materials.unit_cost) as cost')
            ->first();

        return $recipe->number_lines ? (float) $recipe->cost : null;
    }

    public static function dispatchIntermediatesOf(int $artefactId): void
    {
        RawMaterial::where('type', RawMaterialTypeEnum::INTERMEDIATE)
            ->where('artefact_id', $artefactId)
            ->each(fn (RawMaterial $intermediate) => static::dispatch($intermediate)->afterCommit());
    }

    public static function dispatchIntermediatesUsing(RawMaterial $rawMaterial): void
    {
        RawMaterial::where('type', RawMaterialTypeEnum::INTERMEDIATE)
            ->whereIn('artefact_id', RecipeStepRawMaterial::query()
                ->join('artefacts_manufacture_tasks', 'artefacts_manufacture_tasks.id', 'recipe_step_raw_materials.artefact_manufacture_task_id')
                ->where('recipe_step_raw_materials.raw_material_id', $rawMaterial->id)
                ->select('artefacts_manufacture_tasks.artefact_id'))
            ->where('id', '!=', $rawMaterial->id)
            ->each(fn (RawMaterial $intermediate) => static::dispatch($intermediate)->afterCommit());
    }
}
