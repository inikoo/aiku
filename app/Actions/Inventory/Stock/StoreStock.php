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
use App\Actions\Organisation\Organisation\Hydrators\OrganisationHydrateInventory;
use App\Models\CRM\Customer;
use App\Models\Inventory\Stock;
use App\Models\Inventory\StockFamily;
use App\Models\Organisation\Organisation;
use App\Rules\CaseSensitive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreStock
{
    use AsAction;
    use WithAttributes;

    public function handle(Organisation|Customer|StockFamily $owner, $modelData): Stock
    {
        if (class_basename($owner) === 'StockFamily') {
            $modelData['owner_type'] = "StockFamily";
        } elseif (class_basename($owner) === 'Customer') {
            $modelData['owner_type'] = "Customer";
        } else {
            $modelData['owner_type'] = "Organisation";
        }
        $modelData['owner_id'] = $owner->id;
        /** @var Stock $stock */
        $stock = $owner->stocks()->create($modelData);
        $stock->stats()->create();
        OrganisationHydrateInventory::dispatch(app('currentTenant'));
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
            'code'          => ['required', 'unique:locations', 'between:2,64', 'alpha_dash', new CaseSensitive('stocks')],
            /*'code' => [
                'required', 'alpha_dash',
                Rule::unique('stocks', 'code')->where(
                    fn ($query) => $query->where('owner_type', 'Customer')->where('owner_id', $request->user()?->customer->id)
                )
            ], */
            'name'  => ['required','max:255']
        ];
    }

    public function action(Organisation|Customer|StockFamily $owner, $objectData): Stock
    {
        return $this->handle($owner, $objectData);
    }

    public function inStockFamily(StockFamily $stockFamily, ActionRequest $request): Stock
    {
        $request->validate();

        return $this->handle($stockFamily, $request->validated());
    }

    public function htmlResponse(Stock $stock): RedirectResponse
    {
        if(!$stock->stock_family_id) {
            return Redirect::route('inventory.stock-families.show.stocks.show', [
                $stock->stockFamily->slug,
                $stock->slug
            ]);
        } else {
            return Redirect::route('inventory.stocks.show', [
                $stock->slug
            ]);
        }
    }
}
