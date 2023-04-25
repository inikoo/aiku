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

    private bool $asAction=false;

    public function handle(WarehouseArea $warehouseArea, array $modelData): WarehouseArea
    {
        $warehouseArea = $this->update($warehouseArea, $modelData, ['data']);
        WarehouseAreaHydrateUniversalSearch::dispatch($warehouseArea);
        return $warehouseArea;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    }
    public function rules(): array
    {
        return [
            'code'         => ['sometimes', 'required', 'unique:tenant.warehouses', 'between:2,4', 'alpha'],
            'name'         => ['sometimes', 'required', 'max:250', 'string'],
        ];
    }

    public function action(WarehouseArea $warehouseArea, $objectData): WarehouseArea
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($warehouseArea, $validatedData);
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
