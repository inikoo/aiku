<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Warehouse;

use App\Models\Inventory\Warehouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteWarehouse
{
    use AsController;
    use WithAttributes;

    public function handle(Warehouse $warehouse): Warehouse
    {
        $warehouse->locations()->delete();
        $warehouse->warehouseAreas()->delete();
        $warehouse->delete();

        return $warehouse;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.edit");
    }

    public function asController(Warehouse $warehouse, ActionRequest $request): Warehouse
    {
        $request->validate();

        return $this->handle($warehouse);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('grp.inventory.warehouses.index');
    }

}
