<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock;

use App\Actions\Goods\Stock\Hydrators\StockHydrateUniversalSearch;
use App\Actions\Goods\StockFamily\Hydrators\StockFamilyHydrateStocks;
use App\Actions\GrpAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateStocks;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use App\Models\SysAdmin\Group;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreStock extends GrpAction
{
    public function handle(Group|StockFamily $parent, $modelData): Stock
    {
        data_set($modelData, 'group_id', $this->group->id);

        /** @var Stock $stock */
        $stock = $parent->stocks()->create($modelData);
        $stock->stats()->create();
        GroupHydrateStocks::dispatch($this->group)->delay($this->hydratorsDelay);
        if ($parent instanceof StockFamily) {
            StockFamilyHydrateStocks::dispatch($parent)->delay($this->hydratorsDelay);
        }

        StockHydrateUniversalSearch::dispatch($stock);

        return $stock;
    }

    public function rules(): array
    {
        return [
            'code'        => [
                'required',
                'max:64',
                new AlphaDashDot(),
                Rule::notIn(['export', 'create', 'upload', 'in-process', 'active', 'discontinuing', 'discontinued']),
                new IUnique(
                    table: 'stocks',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                    ]
                ),
            ],
            'name'        => ['required', 'string', 'max:255'],
            'source_id'   => ['sometimes', 'nullable', 'string'],
            'source_slug' => ['sometimes', 'nullable', 'string'],
            'state'       => ['sometimes', 'nullable', Rule::enum(StockStateEnum::class)],
        ];
    }

    public function action(Group|StockFamily $parent, array $modelData, int $hydratorDelay = 0): Stock
    {
        if ($parent instanceof Group) {
            $group = $parent;
        } else {
            $group = $parent->group;
        }


        $this->hydratorsDelay = $hydratorDelay;
        $this->initialisation($group, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    public function inStockFamily(StockFamily $stockFamily, ActionRequest $request): Stock
    {
        $this->initialisation(group(), $request);

        return $this->handle($stockFamily, $this->validatedData);
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
