<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily;

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
        return Redirect::route('grp.org.inventory.stock-families.index');
    }

}
