<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 01:27:39 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Marketing\Shop\UpdateShop;
use App\Models\Marketing\Shop;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchShops extends FetchAction
{


    public string $commandSignature = 'fetch:shops {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Shop
    {
        if ($shopData = $tenantSource->fetchShop($tenantSourceId)) {
            if ($shop = Shop::where('source_id', $shopData['shop']['source_id'])
                ->first()) {
                $shop = UpdateShop::run(
                    shop:      $shop,
                    modelData: $shopData['shop']
                );

                if (!empty($shopData['collectionAddress'])) {
                    if ($collectionAddress = $shop->getAddress('collection')) {
                        UpdateAddress::run($collectionAddress, $shopData['collectionAddress']);
                    } else {
                        StoreAddressAttachToModel::run($shop, $shopData['collectionAddress'], ['scope' => 'collection']);
                    }
                }
            } else {
                $shop = StoreShop::run(
                    tenant:tenant(),
                    modelData: $shopData['shop']
                );

                if (!empty($shopData['collectionAddress'])) {
                    StoreAddressAttachToModel::run($shop, $shopData['collectionAddress'], ['scope' => 'collection']);
                }
            }

            return $shop;
        }

        return null;
    }


    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Store Dimension')
            ->select('Store Key as source_id')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Store Dimension')->count();
    }

}
