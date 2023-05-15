<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 29 Oct 2021 12:56:07 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Actions\Inventory\Stock;

use App\Actions\Inventory\Stock\Hydrators\StockHydrateUniversalSearch;
use App\Actions\Inventory\StockFamily\Hydrators\StockFamilyHydrateStocks;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateInventory;
use App\Models\Inventory\Stock;
use App\Models\Sales\Customer;
use App\Models\Tenancy\Tenant;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreStock
{
    use AsAction;
    use WithAttributes;

    public function handle(Tenant|Customer $owner, $modelData): Stock
    {
        /** @var Stock $stock */
        $stock = $owner->stocks()->create($modelData);
        $stock->stats()->create();

        TenantHydrateInventory::dispatch(app('currentTenant'));
        if ($stock->stock_family_id) {
            StockFamilyHydrateStocks::dispatch($stock->stockFamily);
        }
        StockHydrateUniversalSearch::dispatch($stock);
        HydrateStock::run($stock);

        return $stock;
    }


    public function rules(ActionRequest $request): array
    {
        return [
            'code' => [
                'required',
                Rule::unique('stocks', 'code')->where(
                    fn ($query) => $query->where('owner_type', 'Customer')->where('owner_id', $request->user()?->customer->id)
                )
            ],
        ];
    }

    public function action(Tenant|Customer $owner, $objectData): Stock
    {
        return $this->handle($owner, $objectData);
    }
}
