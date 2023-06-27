<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location;

use App\Models\Inventory\Location;
use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

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


    public function inWarehouse(Warehouse $warehouse, Location $location, ActionRequest $request): Location
    {
        $request->validate();

        return $this->handle($location);
    }

    public function inWarehouseInWarehouseArea(Warehouse $warehouse, WarehouseArea $warehouseArea, Location $location, ActionRequest $request): Location
    {
        $request->validate();

        return $this->handle($location);
    }


    public function htmlResponse(Location $location): RedirectResponse
    {
        dd($location);
        return Redirect::route('inventory.locations.show', $location->slug);
    }

}
