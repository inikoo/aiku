<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 04 Oct 2021 12:35:41 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Actions\Inventory\WarehouseArea\Search\WarehouseAreaRecordSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Inventory\WarehouseAreaResource;
use App\Models\Inventory\WarehouseArea;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateWarehouseArea extends OrgAction
{
    use WithActionUpdate;

    private WarehouseArea $warehouseArea;

    public function handle(WarehouseArea $warehouseArea, array $modelData): WarehouseArea
    {
        $warehouseArea = $this->update($warehouseArea, $modelData, ['data']);
        WarehouseAreaRecordSearch::dispatch($warehouseArea);

        return $warehouseArea;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes',
                'required',
                'max:16',
                'alpha_dash',
                new IUnique(
                    table: 'warehouse_areas',
                    extraConditions: [
                        ['column' => 'warehouse_id', 'value' => $this->warehouseArea->warehouse_id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->warehouseArea->id
                        ]
                    ]
                ),
            ],
            'name'                     => ['sometimes', 'required', 'max:250', 'string'],
            'last_fetched_at'          => ['sometimes', 'date'],
        ];
    }

    public function action(WarehouseArea $warehouseArea, array $modelData, bool $audit =true): WarehouseArea
    {
        if(!$audit) {
            WarehouseArea::disableAuditing();
        }
        $this->asAction      = true;
        $this->warehouseArea = $warehouseArea;
        $this->initialisation($warehouseArea->organisation, $modelData);

        return $this->handle($warehouseArea, $this->validatedData);
    }

    public function asController(WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $this->warehouseArea = $warehouseArea;
        $this->initialisation($warehouseArea->organisation, $request);

        return $this->handle($warehouseArea, $this->validatedData);
    }


    public function jsonResponse(WarehouseArea $warehouseArea): WarehouseAreaResource
    {
        return new WarehouseAreaResource($warehouseArea);
    }
}
