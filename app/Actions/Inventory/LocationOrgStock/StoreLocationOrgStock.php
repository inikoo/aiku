<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 28 Aug 2024 17:52:21 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\LocationOrgStock;

use App\Actions\Inventory\Location\Hydrators\LocationHydrateStocks;
use App\Actions\Inventory\Location\Hydrators\LocationHydrateStockValue;
use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateLocations;
use App\Actions\OrgAction;
use App\Http\Resources\Inventory\LocationOrgStockResource;
use App\Models\Inventory\Location;
use App\Models\Inventory\LocationOrgStock;
use App\Models\Inventory\OrgStock;
use Lorisleiva\Actions\ActionRequest;
use Illuminate\Validation\Validator;

class StoreLocationOrgStock extends OrgAction
{
    use WithLocationOrgStockActionAuthorisation;

    private Location $location;
    private OrgStock $orgStock;



    public function handle(Location $location, OrgStock $orgStock, array $modelData)
    {
        $location->orgStocks()->attach($orgStock->id, $modelData);
        $locationStock = LocationOrgStock::where('location_id', $location->id)->where('org_stock_id', $orgStock->id)
            ->first();


        LocationHydrateStocks::dispatch($location);
        LocationHydrateStockValue::dispatch($location);
        OrgStockHydrateLocations::dispatch($orgStock);
    }

    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if ($this->location->organisation_id != $this->orgStock->organisation_id) {
            $validator->errors()->add('location_org_stock', 'Location / stock organisation does not match');
        }


        if (LocationOrgStock::where('location_id', $this->location->id)->where('org_stock_id', $this->orgStock->id)
                ->count() > 0) {
            $validator->errors()->add('location_org_stock', __('This stock is already assigned to this location'));
        }
    }

    public function asController(OrgStock $orgStock, Location $location, ActionRequest $request)
    {
        $this->location = $location;
        $this->orgStock = $orgStock;
        $this->initialisation($orgStock->organisation, $request);

        $this->handle($location, $orgStock, $this->validatedData);
    }

    public function action(OrgStock $orgStock, Location $location, array $modelData)
    {
        $this->asAction = true;
        $this->location = $location;
        $this->orgStock = $orgStock;
        $this->initialisation($orgStock->organisation, $modelData);

        $this->handle($location, $orgStock, $this->validatedData);
    }

    public function jsonResponse(LocationOrgStock $locationStock): LocationOrgStockResource
    {
        return new LocationOrgStockResource($locationStock);
    }


}
