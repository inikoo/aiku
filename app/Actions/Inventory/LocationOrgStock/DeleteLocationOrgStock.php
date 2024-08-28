<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 28 Aug 2024 22:35:34 Central Indonesia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\LocationOrgStock;

use App\Actions\Inventory\Location\Hydrators\LocationHydrateStocks;
use App\Actions\Inventory\Location\Hydrators\LocationHydrateStockValue;
use App\Actions\Inventory\OrgStock\Hydrators\OrgStockHydrateLocations;
use App\Actions\OrgAction;
use App\Models\Inventory\LocationOrgStock;
use Lorisleiva\Actions\ActionRequest;

class DeleteLocationOrgStock extends OrgAction
{
    public function authorize(ActionRequest $request)
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.{$this->organisation->id}.edit");
    }

    public function handle(LocationOrgStock $locationOrgStock): void
    {
        $location = $locationOrgStock->location;
        $orgStock = $locationOrgStock->orgStock;

        $locationOrgStock->location->orgStocks()->detach($locationOrgStock->org_stock_id);

        LocationHydrateStocks::dispatch($location);
        LocationHydrateStockValue::dispatch($location);
        OrgStockHydrateLocations::dispatch($orgStock);
    }

    public function asController(LocationOrgStock $locationOrgStock, ActionRequest $request): void
    {
        $this->initialisation($locationOrgStock->location->organisation, $request);
        $this->handle($locationOrgStock);
    }

    public function action(LocationOrgStock $locationOrgStock): void
    {
        $this->asAction = true;
        $this->initialisation($locationOrgStock->location->organisation, []);

        $this->handle($locationOrgStock);
    }

}
