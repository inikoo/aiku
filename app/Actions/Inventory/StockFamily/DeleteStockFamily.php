<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily;

use App\Models\Inventory\StockFamily;
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
        return Redirect::route('grp.inventory.stock-families.index');
    }

}
