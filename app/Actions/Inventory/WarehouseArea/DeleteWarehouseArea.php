<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\WarehouseArea;

use App\Models\Inventory\Warehouse;
use App\Models\Inventory\WarehouseArea;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteWarehouseArea
{
    use AsController;
    use WithAttributes;

    public function handle(WarehouseArea $warehouseArea): WarehouseArea
    {
        $warehouseArea->locations()->delete();
        $warehouseArea->stats()->delete();
        $warehouseArea->delete();

        return $warehouseArea;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.edit");
    }

    public function asController(WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $request->validate();

        return $this->handle($warehouseArea);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWarehouse(Warehouse $warehouse, WarehouseArea $warehouseArea, ActionRequest $request): WarehouseArea
    {
        $request->validate();

        return $this->handle($warehouseArea);
    }



    public function htmlResponse(WarehouseArea $warehouseArea): RedirectResponse
    {
        return Redirect::route('grp.org.warehouses.show.infrastructure.dashboard', $warehouseArea->warehouse->slug);
    }

}
