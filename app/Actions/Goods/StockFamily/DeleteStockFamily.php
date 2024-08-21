<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\StockFamily;

use App\Models\SupplyChain\StockFamily;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteStockFamily
{
    use AsController;
    use WithAttributes;

    public function handle(StockFamily $stockFamily): StockFamily
    {
        $stockFamily->stats()->delete();
        $stockFamily->stocks()->delete();
        $stockFamily->delete();
        $stockFamily->delete();

        return $stockFamily;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.stocks.edit");
    }

    public function asController(StockFamily $stockFamily, ActionRequest $request): StockFamily
    {
        $request->validate();

        return $this->handle($stockFamily);
    }

    public function htmlResponse(): RedirectResponse
    {
        return Redirect::route('grp.org.warehouses.show.inventory.org_stock_families.index');
    }

}
