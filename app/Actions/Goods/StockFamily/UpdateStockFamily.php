<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 23 Mar 2024 12:24:25 Malaysia Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Goods\StockFamily;

use App\Actions\Goods\StockFamily\Hydrators\StockFamilyHydrateUniversalSearch;
use App\Actions\GrpAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateStockFamilies;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\SupplyChain\StockFamily\StockFamilyStateEnum;
use App\Http\Resources\Inventory\OrgStockFamiliesResource;
use App\Models\SupplyChain\StockFamily;
use App\Rules\IUnique;
use Illuminate\Support\Arr;
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
        $changes = $stockFamily->getChanges();

        if (Arr::hasAny($changes, ['code', 'name', 'stock_family_id', 'unit_value'])) {
            foreach ($stockFamily->orgStocksFamilies as $orgStockFamily) {
                $orgStockFamily->update(
                    [
                        'code'       => $stockFamily->code,
                        'name'       => $stockFamily->name,
                    ]
                );
            }
        }

        if (Arr::hasAny($stockFamily->getChanges(), ['state'])) {
            GroupHydrateStockFamilies::run($stockFamily->group);
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
            'state'=> ['sometimes', 'required', Rule::enum(StockFamilyStateEnum::class)],
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

    public function jsonResponse(StockFamily $stockFamily): OrgStockFamiliesResource
    {
        return new OrgStockFamiliesResource($stockFamily);
    }
}
