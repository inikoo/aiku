<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 17:49:30 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Asset;

use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateHistoricAssets;
use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateUniversalSearch;
use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateAssets;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateAssets;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateAssets;
use App\Models\Catalogue\Asset;
use App\Models\Catalogue\Charge;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Service;
use App\Models\Fulfilment\Rental;
use App\Models\Ordering\Adjustment;
use App\Models\Ordering\ShippingZone;

class StoreAsset extends OrgAction
{
    public function handle(Product|Rental|Service|Charge|ShippingZone|Adjustment $parent, array $modelData): Asset
    {
        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'shop_id', $parent->shop_id);

        data_set($modelData, 'code', $parent->code);
        data_set($modelData, 'name', $parent->name);
        data_set($modelData, 'price', $parent->price, overwrite: false);
        data_set($modelData, 'unit', $parent->unit, overwrite: false);
        data_set($modelData, 'units', $parent->units, overwrite: false);
        data_set($modelData, 'status', $parent->status);
        data_set($modelData, 'created_at', $parent->created_at);
        data_set($modelData, 'currency_id', $parent->currency_id);

        data_set($modelData, 'model_type', $parent->getMorphClass());
        data_set($modelData, 'model_id', $parent->id);


        /** @var Asset $asset */
        $asset = $parent->asset()->create($modelData);
        $asset->stats()->create();
        $asset->salesIntervals()->create();

        AssetHydrateHistoricAssets::dispatch($asset);
        ShopHydrateAssets::dispatch($asset->shop);
        OrganisationHydrateAssets::dispatch($asset->organisation);
        GroupHydrateAssets::dispatch($asset->group);
        AssetHydrateUniversalSearch::dispatch($asset);

        return $asset;
    }


}
