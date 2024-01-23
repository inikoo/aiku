<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 11:09:26 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\Stock;

use App\Actions\GrpAction;
use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Inventory\StockResource;
use App\Models\SupplyChain\Stock;
use App\Models\SupplyChain\StockFamily;
use App\Rules\AlphaDashDot;
use App\Rules\IUnique;
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
        OrgStockHydrateUniversalSearch::dispatch($stock);

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


    public function jsonResponse(Stock $stock): StockResource
    {
        return new StockResource($stock);
    }
}
