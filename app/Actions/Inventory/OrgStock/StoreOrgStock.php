<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 08:52:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\Inventory\OrgStock\Hydrators\StockHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Models\Inventory\OrgStock;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgStock extends OrgAction
{
    public function handle(Organisation $organisation, Stock $stock, $modelData): OrgStock
    {

        data_set($modelData, 'group_id', $organisation->group_id);
        data_set($modelData, 'or', $organisation->group_id);


        $orgStock = $stock->orgStock()->create($modelData);
        $orgStock->stats()->create();

        //StockHydrateUniversalSearch::dispatch($stock);



        return $orgStock;
    }


    public function rules(ActionRequest $request): array
    {
        return [

        ];
    }

    public function action(Organisation $organisation, Stock $stock, $modelData): OrgStock
    {
        $this->asAction = true;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $stock, $this->validatedData);
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
            return Redirect::route('grp.org.inventory.stock-families.show.stocks.show', [
                $stock->stockFamily->slug,
                $stock->slug
            ]);
        } else {
            return Redirect::route('grp.org.inventory.stocks.show', [
                $stock->slug
            ]);
        }
    }
}
