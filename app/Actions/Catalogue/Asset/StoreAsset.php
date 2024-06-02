<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 17:49:30 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Catalogue\Asset;

use App\Actions\Catalogue\Shop\Hydrators\ShopHydrateAssets;
use App\Actions\SysAdmin\Group\Hydrators\GroupHydrateAssets;
use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateHistoricAssets;
use App\Actions\Catalogue\Asset\Hydrators\AssetHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateAssets;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Service;
use App\Models\Fulfilment\Rental;
use App\Models\Catalogue\Asset;

class StoreAsset extends OrgAction
{
    public function handle(Product|Rental|Service $parent, array $modelData): Asset
    {

        data_set($modelData, 'group_id', $parent->group_id);
        data_set($modelData, 'organisation_id', $parent->organisation_id);
        data_set($modelData, 'shop_id', $parent->shop_id);

        data_set($modelData, 'code', $parent->code);
        data_set($modelData, 'name', $parent->name);
        data_set($modelData, 'price', $parent->price);
        data_set($modelData, 'unit', $parent->unit);
        data_set($modelData, 'number_units', $parent->number_units);
        data_set($modelData, 'status', $parent->status);
        data_set($modelData, 'created_at', $parent->created_at);
        data_set($modelData, 'currency_id', $parent->currency_id);

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
