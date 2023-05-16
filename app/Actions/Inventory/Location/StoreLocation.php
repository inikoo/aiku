<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Location;

use App\Actions\Inventory\Location\Hydrators\LocationHydrateUniversalSearch;
use App\Actions\Inventory\Warehouse\HydrateWarehouse;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreLocation
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;

    public function handle(WarehouseArea|Warehouse $parent, array $modelData): Location
    {
        if (class_basename($parent::class) == 'WarehouseArea') {
            $modelData['warehouse_id'] = $parent->warehouse_id;
        } else {
            HydrateWarehouse::run($parent);
        }

        /** @var Location $location */
        $location = $parent->locations()->create($modelData);
        $location->stats()->create();

        LocationHydrateUniversalSearch::dispatch($location);

        return $location;
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
            'code'         => ['required', 'unique:tenant.locations', 'between:2,64', 'alpha_dash'],
        ];
    }

    public function action(WarehouseArea|Warehouse $parent, array $objectData): Location
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }
}
