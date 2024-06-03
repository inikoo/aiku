<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\Stock;

use App\Actions\Goods\Stock\Hydrators\StockHydrateUniversalSearch;
use App\Actions\GrpAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateStocks;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Http\Resources\Inventory\OrgStockResource;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateStock extends GrpAction
{
    use WithActionUpdate;

    private StockFamily $stockFamily;

    private Stock $stock;

    public function handle(Stock $stock, array $modelData): Stock
    {
        $stock = $this->update($stock, $modelData, ['data', 'settings']);
        StockHydrateUniversalSearch::dispatch($stock);

        if (Arr::hasAny($stock->getChanges(), ['state'])) {
            GroupHydrateStocks::dispatch($stock->group);
        }

        return $stock;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.stocks.edit");
    }

    public function rules(): array
    {
        return [
            'code'            => [
                'sometimes',
                'required',
                new AlphaDashDot(),
                'max:32',
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'stocks',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->stock->id
                        ],

                    ]
                ),
            ],
            'name'            => ['sometimes', 'required', 'string', 'max:255'],
            'stock_family_id' => ['sometimes', 'nullable', 'exists:stock_families,id'],
            'state'           => ['sometimes', 'required', Rule::enum(StockStateEnum::class)],
        ];
    }


    public function action(Stock $stock, array $modelData): Stock
    {
        $this->asAction = true;
        $this->stock    = $stock;
        $this->initialisation($stock->group, $modelData);

        return $this->handle($stock, $this->validatedData);
    }

    public function asController(Stock $stock, ActionRequest $request): Stock
    {
        $this->stock = $stock;
        $this->initialisation($stock->group, $request);

        return $this->handle($stock, $this->validatedData);
    }


    public function jsonResponse(Stock $stock): OrgStockResource
    {
        return new OrgStockResource($stock);
    }
}
