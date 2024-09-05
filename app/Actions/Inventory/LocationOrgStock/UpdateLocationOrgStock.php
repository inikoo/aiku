<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 30 Aug 2024 19:44:08 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\LocationOrgStock;

use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateQuantityInLocations;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Inventory\LocationStock\LocationStockTypeEnum;
use App\Http\Resources\Inventory\LocationOrgStockResource;
use App\Models\Inventory\Location;
use App\Models\Inventory\LocationOrgStock;
use Illuminate\Validation\Rule;
use Lorisleiva\Actions\ActionRequest;

class UpdateLocationOrgStock extends OrgAction
{
    use WithActionUpdate;

    private LocationOrgStock $locationOrgStock;

    private Location $location;

    public function handle(LocationOrgStock $locationOrgStock, array $modelData): LocationOrgStock
    {
        $locationOrgStock= $this->update($locationOrgStock, $modelData, ['data']);

        if($locationOrgStock->wasChanged('quantity')) {

            OrgStockHydrateQuantityInLocations::dispatch($locationOrgStock->orgStock);
        }

        return $locationOrgStock;

    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.locations.edit");
    }

    public function rules(): array
    {
        $rules = [
            'data'               => ['sometimes', 'array'],
            'settings'           => ['sometimes', 'array'],
            'notes'              => ['sometimes', 'nullable', 'string', 'max:255'],
            'source_stock_id'    => ['sometimes', 'string', 'max:255'],
            'source_location_id' => ['sometimes', 'string', 'max:255'],
            'picking_priority'   => ['sometimes', 'integer'],
            'type'               => ['sometimes', Rule::enum(LocationStockTypeEnum::class)],
        ];

        if (!$this->strict) {
            $rules['audited_at']        = ['date'];
            $rules['quantity']          = ['sometimes', 'numeric'];
            $rules['last_fetched_at']   = ['sometimes', 'date'];
        }

        return $rules;
    }

    public function action(LocationOrgStock $locationOrgStock, array $modelData, int $hydratorsDelay = 0, bool $strict = true, bool $audit=true): LocationOrgStock
    {
        if(!$audit) {
            LocationOrgStock::disableAuditing();
        }
        $this->hydratorsDelay = $hydratorsDelay;
        $this->strict         = $strict;
        $this->asAction       = true;
        $this->initialisation($locationOrgStock->organisation, $modelData);
        return $this->handle($locationOrgStock, $this->validatedData);
    }

    public function asController(LocationOrgStock $locationOrgStock, ActionRequest $request): LocationOrgStock
    {
        $this->initialisation($locationOrgStock->organisation, $request);

        return $this->handle($locationOrgStock, $this->validatedData);
    }

    public function jsonResponse(LocationOrgStock $locationOrgStock): LocationOrgStockResource
    {
        return new LocationOrgStockResource($locationOrgStock);
    }
}
