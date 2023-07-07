<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 21:01:30 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily;

use App\Actions\Inventory\StockFamily\Hydrators\StockFamilyHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Inventory\StockFamilyResource;
use App\Models\Inventory\StockFamily;
use Lorisleiva\Actions\ActionRequest;

class UpdateStockFamily
{
    use WithActionUpdate;


    public function handle(StockFamily $stockFamily, array $modelData): StockFamily
    {
        $stockFamily = $this->update($stockFamily, $modelData, ['data']);
        StockFamilyHydrateUniversalSearch::dispatch($stockFamily);
        return $stockFamily;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.stocks.edit");
    }
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required'],
            'name' => ['sometimes', 'required'],
        ];
    }


    public function asController(StockFamily $stockFamily, ActionRequest $request): StockFamily
    {
        $request->validate();
        return $this->handle($stockFamily, $request->all());
    }


    public function jsonResponse(StockFamily $stockFamily): StockFamilyResource
    {
        return new StockFamilyResource($stockFamily);
    }
}
