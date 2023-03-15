<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 04 Oct 2021 12:35:41 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\Inventory\WarehouseArea\Hydrators\WarehouseAreaHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\Models\Inventory\WarehouseArea;
use Lorisleiva\Actions\ActionRequest;

class UpdateWarehouseArea
{
    use WithActionUpdate;

    public function handle(WarehouseArea $warehouseArea, array $modelData): WarehouseArea
    {
        $warehouseArea = $this->update($warehouseArea, $modelData, ['data']);
        WarehouseAreaHydrateUniversalSearch::dispatch($warehouseArea);
        return $warehouseArea;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    }
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required'],
            'name' => ['sometimes', 'required'],
        ];
    }


    public function asController(WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $request->validate();
        return $this->handle($warehouseArea, $request->all());
    }


    public function jsonResponse(WarehouseArea $warehouseArea): WarehouseAreaResource
    {
        return new WarehouseAreaResource($warehouseArea);
    }
}
