<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 04 Oct 2021 12:34:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\Inventory\WarehouseArea\Hydrators\WarehouseAreaHydrateUniversalSearch;
use App\Models\Inventory\WarehouseArea;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreWarehouseArea
{
    use AsAction;
    use WithAttributes;

    public function handle(Warehouse $warehouse, array $modelData): WarehouseArea
    {
        /** @var WarehouseArea $warehouseArea */
        $warehouseArea= $warehouse->warehouseAreas()->create($modelData);
        $warehouseArea->stats()->create();
        WarehouseAreaHydrateUniversalSearch::dispatch($warehouseArea);

        return $warehouseArea;
    }

    public function rules(): array
    {
        return [
            'code'         => ['required', 'unique:tenant.warehouses', 'between:2,4', 'alpha'],
            'name'         => ['required', 'max:250', 'string'],
        ];
    }

    public function action(Warehouse $warehouse, array $objectData): WarehouseArea
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($warehouse, $validatedData);
    }
}
