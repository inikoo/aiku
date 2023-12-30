<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 21:01:11 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily;

use App\Actions\Inventory\StockFamily\Hydrators\StockFamilyHydrateUniversalSearch;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateInventory;
use App\Models\SysAdmin\Group;
use App\Models\Inventory\StockFamily;
use App\Rules\CaseSensitive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreStockFamily
{
    use AsAction;
    use WithAttributes;

    private bool $asAction = false;

    public function handle(Group $group, $modelData): StockFamily
    {
        /** @var StockFamily $stockFamily */
        $stockFamily = $group->stockFamilies()->create($modelData);
        $stockFamily->stats()->create();
        GroupHydrateInventory::dispatch(group());
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
            'code' => ['required', 'unique:stock_families', 'between:2,9', 'alpha_dash', new CaseSensitive('stock_families')],
            'name' => ['required', 'string']
        ];
    }

    public function action(Group $group, array $modelData): StockFamily
    {
        $this->asAction = true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($group, $validatedData);
    }

    public function asController(ActionRequest $request): StockFamily
    {
        $request->validate();

        return $this->handle(group(), $request->validated());
    }


    public function htmlResponse(StockFamily $stockFamily): RedirectResponse
    {
        return Redirect::route('grp.inventory.stock-families.index');
    }
}
