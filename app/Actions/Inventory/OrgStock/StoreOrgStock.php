<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 08:52:47 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateUniversalSearch;
use App\Actions\Inventory\OrgStockFamily\StoreOrgStockFamily;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateOrgStocks;
use App\Enums\Inventory\OrgStock\OrgStockQuantityStatusEnum;
use App\Enums\Inventory\OrgStock\OrgStockStateEnum;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Models\Inventory\OrgStock;
use App\Models\SupplyChain\Stock;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreOrgStock extends OrgAction
{
    private Stock $stock;

    public function handle(Organisation $organisation, Stock $stock, $modelData): OrgStock
    {
        data_set($modelData, 'group_id', $organisation->group_id);
        data_set($modelData, 'organisation_id', $organisation->id);


        data_set($modelData, 'code', $stock->code);
        data_set($modelData, 'name', $stock->name);
        data_set($modelData, 'unit_value', $stock->unit_value);


        /** @var OrgStock $orgStock */
        $orgStock = $stock->orgStocks()->create($modelData);
        $orgStock->stats()->create(
            [
                'group_id'        => $organisation->group_id,
                'organisation_id' => $organisation->id,
            ]
        );

        if ($stockFamily = $stock->stockFamily) {
            if (!$orgStockFamily = $stockFamily->orgStockFamilies()->where('organisation_id', $organisation->id)->first()) {
                $orgStockFamily = StoreOrgStockFamily::run($organisation, $stockFamily, []);
            }
            $orgStock->orgStockFamily()->associate($orgStockFamily);
            $orgStock->save();
        }

        OrgStockHydrateUniversalSearch::dispatch($orgStock);
        OrganisationHydrateOrgStocks::dispatch($organisation);

        $orgStock->refresh();

        return $orgStock;
    }


    public function rules(ActionRequest $request): array
    {
        return [
            'state'           => ['required', Rule::enum(OrgStockStateEnum::class)],
            'quantity_status' => ['sometimes', 'nullable', Rule::enum(OrgStockQuantityStatusEnum::class)],
            'source_id'       => ['sometimes', 'nullable', 'string'],
        ];
    }


    public function prepareForValidation(): void
    {
        $state = match ($this->stock->state) {
            StockStateEnum::ACTIVE        => OrgStockStateEnum::ACTIVE,
            StockStateEnum::DISCONTINUING => OrgStockStateEnum::DISCONTINUING,
            StockStateEnum::DISCONTINUED  => OrgStockStateEnum::DISCONTINUED,
            StockStateEnum::SUSPENDED     => OrgStockStateEnum::SUSPENDED,
            default                       => null
        };


        $this->set('state', $state);
    }

    public function action(Organisation $organisation, Stock $stock, $modelData=[], $hydratorDelay = 0): OrgStock
    {
        $this->asAction       = true;
        $this->stock          = $stock;
        $this->hydratorsDelay = $hydratorDelay;
        $this->initialisation($organisation, $modelData);

        return $this->handle($organisation, $stock, $this->validatedData);
    }


    public function htmlResponse(Stock $stock): RedirectResponse
    {
        if (!$stock->stock_family_id) {
            return Redirect::route('grp.org.inventory.org-stock-families.show.stocks.show', [
                $stock->stockFamily->slug,
                $stock->slug
            ]);
        } else {
            return Redirect::route('grp.org.inventory.org-stocks.show', [
                $stock->slug
            ]);
        }
    }
}
