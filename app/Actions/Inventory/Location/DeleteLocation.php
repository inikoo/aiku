<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location;

use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Models\Inventory\Location;

class DeleteLocation
{
    use AsController;
    use WithAttributes;

    public function handle(Location $location): Location
    {
        $location->delete();

        return $location;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.edit");
    }

    public function asController(Location $location, ActionRequest $request): Location
    {
        $request->validate();

        return $this->handle($location);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, Location $location, ActionRequest $request): Location
    {
        $request->validate();

        return $this->handle($location);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseArea(WarehouseArea $warehouseArea, Location $location, ActionRequest $request): Location
    {
        $request->validate();

        return $this->handle($location);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseInWarehouseArea(Warehouse $warehouse, WarehouseArea $warehouseArea, Location $location, ActionRequest $request): Location
    {
        $request->validate();

        return $this->handle($location);
    }


    public function htmlResponse(Warehouse | WarehouseArea | Location $parent): RedirectResponse
    {
        if (class_basename($parent::class) == 'WarehouseArea') {
            return Redirect::route(
                route: 'grp.org.hr.workplace.show.clocking_machines.show.locations.index',
                parameters: [
                    'warehouse'       => $parent->warehouse->slug,
                    'warehouseArea'   => $parent->slug
                ]
            );

        } elseif (class_basename($parent::class) == 'Warehouse') {
            return Redirect::route(
                route: 'grp.org.warehouses.show.inventory.warehouse-areas.show.locations.index',
                parameters: [
                    'warehouse' => $parent->slug
                ]
            );
        } else {
            return Redirect::route('grp.org.warehouses.show.inventory.locations.index');
        }
    }

}
