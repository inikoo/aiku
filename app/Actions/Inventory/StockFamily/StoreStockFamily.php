<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 21:01:11 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily;

use App\Actions\Inventory\StockFamily\Hydrators\StockFamilyHydrateUniversalSearch;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateInventory;
use App\Models\Inventory\StockFamily;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreStockFamily
{
    use AsAction;
    use WithAttributes;

    public function handle($modelData): StockFamily
    {
        /** @var StockFamily $stockFamily */
        $stockFamily = StockFamily::create($modelData);
        $stockFamily->stats()->create();
        TenantHydrateInventory::dispatch(app('currentTenant'));
        StockFamilyHydrateUniversalSearch::dispatch($stockFamily);

        return $stockFamily;
    }

    public function rules(): array
    {
        return [
            'code'  => ['required', 'unique:tenant.stock_families', 'between:2,9', 'alpha'],
            'name'  => ['required', 'string']
        ];
    }

    public function action($objectData): StockFamily
    {
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }
}
