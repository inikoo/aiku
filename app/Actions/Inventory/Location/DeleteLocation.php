<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location;

use App\Actions\OrgAction;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;
use App\Models\Inventory\Location;

class DeleteLocation extends OrgAction
{
    use AsController;
    use WithAttributes;

    private Warehouse|WarehouseArea $parent;

    public function handle(Location $location): Location
    {
        $location->delete();

        return $location;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("locations.{$this->warehouse->id}.edit");
    }

    public function asController(Location $location, ActionRequest $request): Location
    {
        $request->validate();

        return $this->handle($location);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, Location $location, ActionRequest $request): Location
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($location);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseArea(WarehouseArea $warehouseArea, Location $location, ActionRequest $request): Location
    {
        $this->parent = $warehouseArea;
        $this->initialisationFromWarehouse($warehouseArea->warehouse, $request);

        return $this->handle($location);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouseInWarehouseArea(Warehouse $warehouse, WarehouseArea $warehouseArea, Location $location, ActionRequest $request): Location
    {
        $request->validate();

        return $this->handle($location);
    }


    public function htmlResponse(): RedirectResponse
    {
        if ($this->parent instanceof Warehouse) {
            return Redirect::route(
                route: 'grp.org.warehouses.show.infrastructure.locations.index',
                parameters: [
                    'organisation'       => $this->parent->organisation->slug,
                    'warehouse'          => $this->parent->slug
                ]
            );

        } elseif ($this->parent instanceof WarehouseArea) {
            return Redirect::route(
                route: 'grp.org.warehouses.show.infrastructure.warehouse-areas.show',
                parameters: [
                    'organisation'       => $this->parent->organisation->slug,
                    'warehouse'          => $this->parent->warehouse->slug,
                    'warehouseArea'      => $this->parent->slug
                ]
            );
        } else {
            return Redirect::route('grp.org.warehouses.show.inventory.locations.index');
        }
    }

}
