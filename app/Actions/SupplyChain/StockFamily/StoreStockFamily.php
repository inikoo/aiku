<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 22 Jan 2024 13:06:36 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\SupplyChain\StockFamily;

use App\Actions\GrpAction;
use App\Actions\SupplyChain\StockFamily\Hydrators\StockFamilyHydrateUniversalSearch;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateInventory;
use App\Models\SupplyChain\StockFamily;
use App\Models\SysAdmin\Group;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class StoreStockFamily extends GrpAction
{
    public function handle(Group $group, $modelData): StockFamily
    {
        /** @var StockFamily $stockFamily */
        $stockFamily = $group->stockFamilies()->create($modelData);
        $stockFamily->stats()->create();
        GroupHydrateInventory::dispatch($this->group);
        StockFamilyHydrateUniversalSearch::dispatch($stockFamily);

        return $stockFamily;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    }

    public function rules(): array
    {
        return [
            'code'        => [
                'required',
                'alpha_dash',
                'max:32',
                Rule::notIn(['export', 'create', 'upload']),
                new IUnique(
                    table: 'stock_families',
                    extraConditions: [
                        ['column' => 'group_id', 'value' => $this->group->id],
                    ]
                ),
            ],
            'name'        => ['required', 'required', 'string', 'max:255'],
            'source_id'   => ['sometimes', 'nullable', 'string'],
            'source_slug' => ['sometimes', 'nullable', 'string'],

        ];
    }

    public function action(Group $group, array $modelData): StockFamily
    {
        $this->asAction = true;
        $this->initialisation($group, $modelData);

        return $this->handle($group, $this->validatedData);
    }

    public function asController(ActionRequest $request): StockFamily
    {
        $request->validate();

        return $this->handle(group(), $request->validated());
    }


    public function htmlResponse(StockFamily $stockFamily): RedirectResponse
    {
        return Redirect::route('grp.org.inventory.stock-families.index');
    }
}
