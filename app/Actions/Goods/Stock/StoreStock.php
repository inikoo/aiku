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
use App\Actions\Traits\Rules\WithNoStrictRules;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Enums\UI\SupplyChain\StockFamilyTabsEnum;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use App\Models\SysAdmin\Group;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreStock extends GrpAction
{
    use WithNoStrictRules;

    /**
     * @throws \Throwable
     */
    public function handle(Group|StockFamily $parent, $modelData): Stock
    {
        data_set($modelData, 'group_id', $this->group->id);

        $stock = DB::transaction(function () use ($parent, $modelData) {
            /** @var Stock $stock */
            $stock = $parent->stocks()->create($modelData);
            $stock->stats()->create();

            return $stock;
        });
        GroupHydrateStocks::dispatch($this->group)->delay($this->hydratorsDelay);
        if ($parent instanceof StockFamily) {
            StockFamilyHydrateStocks::dispatch($parent)->delay($this->hydratorsDelay);
        }

        StockHydrateUniversalSearch::dispatch($stock);

        return $stock;
    }

    public function rules(): array
    {
        $rules = [
            'code'  => [
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
            'name'  => ['required', 'string', 'max:255'],
            'state' => ['sometimes', 'nullable', Rule::enum(StockStateEnum::class)],
        ];

        if (!$this->strict) {
            $rules['source_slug'] = ['sometimes', 'nullable', 'string'];
            $rules                = $this->noStrictStoreRules($rules);
        }

        return $rules;
    }

    /**
     * @throws \Throwable
     */
    public function action(Group|StockFamily $parent, array $modelData, int $hydratorsDelay = 0, bool $strict = true, $audit = true): Stock
    {
        if (!$audit) {
            Stock::disableAuditing();
        }

        if ($parent instanceof Group) {
            $group = $parent;
        } else {
            $group = $parent->group;
        }


        $this->asAction       = true;
        $this->strict         = $strict;
        $this->hydratorsDelay = $hydratorsDelay;

        $this->initialisation($group, $modelData);

        return $this->handle($parent, $this->validatedData);
    }

    /**
     * @throws \Throwable
     */
    public function inStockFamily(StockFamily $stockFamily, ActionRequest $request): Stock
    {
        $this->initialisation(group(), $request);

        return $this->handle($stockFamily, $this->validatedData);
    }

    public function asController(ActionRequest $request): Stock
    {

        $this->initialisation(group(), $request);

        return $this->handle(group(), $this->validatedData);
    }

    public function htmlResponse(Stock $stock): RedirectResponse
    {
        if (!$stock->stock_family_id) {
            return Redirect::route('grp.goods.stocks.show', [
                $stock->slug
            ]);
        } else {
            return Redirect::route('grp.goods.stock-families.show', [
                $stock->stockFamily->slug,
                'tab' => StockFamilyTabsEnum::STOCKS
            ]);
        }
    }
}
