<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Location;

use App\Actions\Inventory\Location\Hydrators\LocationHydrateUniversalSearch;
use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreLocation
{
    use AsAction;
    use WithAttributes;


    public function handle(WarehouseArea|Warehouse $parent, array $modelData): Location
    {
        if (class_basename($parent::class) == 'WarehouseArea') {
            $modelData['warehouse_id'] = $parent->warehouse_id;
        }
        /** @var Location $location */
        $location = $parent->locations()->create($modelData);
        $location->stats()->create();

        LocationHydrateUniversalSearch::dispatch($location);

        return $location;
    }
    public function rules(): array
    {
        return [
            'code'         => ['required', 'unique:tenant.warehouses', 'between:2,4', 'alpha'],
            'name'         => ['required', 'max:250', 'string'],
        ];
    }

    public function action(WarehouseArea|Warehouse $parent, array $objectData): Location
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }
}
