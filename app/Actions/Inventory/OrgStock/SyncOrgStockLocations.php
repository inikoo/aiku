<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 23 Jan 2024 11:39:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\OrgStock;

use App\Actions\Inventory\LocationOrgStock\AuditLocationOrgStock;
use App\Actions\Inventory\LocationOrgStock\DeleteLocationOrgStock;
use App\Actions\Inventory\LocationOrgStock\StoreLocationOrgStock;
use App\Models\Inventory\Location;
use App\Models\Inventory\LocationOrgStock;
use App\Models\Inventory\OrgStock;
use Lorisleiva\Actions\Concerns\AsAction;

class SyncOrgStockLocations
{
    use AsAction;

    public function handle(OrgStock $orgStock, array $locationsData): array
    {

        $oldLocations = $orgStock->locationOrgStocks()->pluck('location_id')->toArray();

        foreach ($locationsData as $locationID=>$locationOrgStockData) {

            if($locationOrgStock = LocationOrgStock::where('org_stock_id', $orgStock->id)->where('location_id', $locationID)->first()) {
                // todo update locationOrgStock
                //AuditLocationOrgStock::make()->action($locationOrgStock, $locationOrgStockData);
            } else {
                /** @var Location $location */
                $location=Location::find($locationID);
                StoreLocationOrgStock::make()->action($orgStock, $location, $locationOrgStockData);
            }



        }



        $newLocations = $orgStock->locationOrgStocks()->pluck('location_id')->toArray();


        foreach(array_diff($oldLocations, $newLocations) as $locationID) {
            $locationOrgStock = LocationOrgStock::where('org_stock_id', $orgStock->id)->where('location_id', $locationID)->first();
            AuditLocationOrgStock::make()->action($locationOrgStock, ['quantity' => 0]);
            DeleteLocationOrgStock::make()->action($locationOrgStock);

        }



        return $newLocations;
    }
}
