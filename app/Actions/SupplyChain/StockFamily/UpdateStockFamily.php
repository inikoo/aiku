<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily;

use App\Actions\GrpAction;
use App\Actions\SupplyChain\StockFamily\Hydrators\StockFamilyHydrateUniversalSearch;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateSupplyChain;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Inventory\StockFamilyResource;
use App\Models\SupplyChain\StockFamily;
use App\Rules\IUnique;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateStockFamily extends GrpAction
{
    use WithActionUpdate;
    private StockFamily $stockFamily;


    public function handle(StockFamily $stockFamily, array $modelData): StockFamily
    {
        $stockFamily = $this->update($stockFamily, $modelData, ['data']);
        StockFamilyHydrateUniversalSearch::dispatch($stockFamily);

        if ($stockFamily->wasChanged('state')) {
            GroupHydrateSupplyChain::dispatch(group());
        }

        return $stockFamily;
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
            'code' => [
                'sometimes',
                'required',
                'alpha_dash',
                'max:32',
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'stock_families',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->stockFamily->id
                        ],

                    ]
                ),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
        ];
    }

    public function action(StockFamily $stockFamily, array $modelData): StockFamily
    {
        $this->asAction    = true;
        $this->stockFamily = $stockFamily;
        $this->initialisation($stockFamily->group, $modelData);

        return $this->handle($stockFamily, $this->validatedData);
    }

    public function asController(StockFamily $stockFamily, ActionRequest $request): StockFamily
    {
        $this->stockFamily = $stockFamily;
        $this->initialisation($stockFamily->group, $request);

        return $this->handle($stockFamily, $this->validatedData);
    }

    public function jsonResponse(StockFamily $stockFamily): StockFamilyResource
    {
        return new StockFamilyResource($stockFamily);
    }
}
