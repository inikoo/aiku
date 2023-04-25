<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 04 Oct 2021 11:48:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\Warehouse;

use App\Actions\Inventory\Warehouse\Hydrators\WarehouseHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Inventory\WarehouseResource;
use App\Models\Inventory\Warehouse;
use Lorisleiva\Actions\ActionRequest;

class UpdateWarehouse
{
    use WithActionUpdate;


    private bool $asAction=false;

    public function handle(Warehouse $warehouse, array $modelData): Warehouse
    {
        $warehouse = $this->update($warehouse, $modelData, ['data','settings']);
        WarehouseHydrateUniversalSearch::dispatch($warehouse);
        return $warehouse;
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


    public function asController(Warehouse $warehouse, ActionRequest $request): Warehouse
    {
        $request->validate();
        return $this->handle($warehouse, $request->all());
    }

    public function action(Warehouse $warehouse, $objectData): Warehouse
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($warehouse, $validatedData);
    }

    public function jsonResponse(Warehouse $warehouse): WarehouseResource
    {
        return new WarehouseResource($warehouse);
    }
}
