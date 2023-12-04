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
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateInventory;
use App\Models\SysAdmin\Group;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreStock
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(Group $group, $modelData): Stock
    {
        /** @var Stock $stock */
        $stock = $group->stocks()->create($modelData);
        $stock->stats()->create();
        GroupHydrateInventory::dispatch(group());
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
            'code'            => ['required', 'iunique:stocks', 'between:2,64', 'alpha_dash'],
            'name'            => ['required', 'max:255'],
            'stock_family_id' => ['sometimes', 'nullable', 'exists:stock_families,id'],
        ];
    }

    public function action(Group $group, $objectData): Stock
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($group, $validatedData);
    }

    public function inStockFamily(StockFamily $stockFamily, ActionRequest $request): Stock
    {
        $this->fillFromRequest($request);
        $this->fill(
            [
                'stock_family_id' => $stockFamily->id
            ]
        );

        $request->validate();

        return $this->handle(group(), $request->validated());
    }

    public function htmlResponse(Stock $stock): RedirectResponse
    {
        if (!$stock->stock_family_id) {
            return Redirect::route('grp.inventory.stock-families.show.stocks.show', [
                $stock->stockFamily->slug,
                $stock->slug
            ]);
        } else {
            return Redirect::route('grp.inventory.stocks.show', [
                $stock->slug
            ]);
        }
    }
}
