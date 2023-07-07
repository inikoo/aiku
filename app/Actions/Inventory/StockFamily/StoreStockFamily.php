<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 24 Oct 2022 21:01:11 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\StockFamily;

use App\Actions\Inventory\StockFamily\Hydrators\StockFamilyHydrateUniversalSearch;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateInventory;
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

    private bool $asAction=false;

    public function handle($modelData): StockFamily
    {
        /** @var StockFamily $stockFamily */
        $stockFamily = StockFamily::create($modelData);
        $stockFamily->stats()->create();
        TenantHydrateInventory::dispatch(app('currentTenant'));
        StockFamilyHydrateUniversalSearch::dispatch($stockFamily);

        return $stockFamily;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("inventory.warehouses.edit");
    }

    public function rules(): array
    {
        return [
            'code'  => ['required', 'unique:tenant.stock_families', 'between:2,9', 'alpha_dash', new CaseSensitive('stock_families')],
            'name'  => ['required', 'string']
        ];
    }

    public function action(array $objectData): StockFamily
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($validatedData);
    }

    public function asController(ActionRequest $request): StockFamily
    {
        $request->validate();

        return $this->handle($request->validated());
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function htmlResponse(StockFamily $stockFamily): RedirectResponse
    {
        return Redirect::route('inventory.stock-families.index');
    }
}
