<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 04 Nov 2022 11:17:38 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Marketing\Shop\StoreShop;
use App\Actions\Marketing\Shop\UpdateShop;
use App\Models\Marketing\Shop;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use Illuminate\Console\Command;

class FetchShopCategories extends FetchAction
{
    public string $commandSignature = 'fetch:shop-categories {tenants?*} {--s|source_id=} {--d|db_suffix=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Shop
    {
        if ($shopData = $tenantSource->fetchShop($tenantSourceId)) {
            if ($shop = Shop::where('source_id', $shopData['shop']['source_id'])
                ->first()) {
                $shop = UpdateShop::run(
                    shop:      $shop,
                    modelData: $shopData['shop']
                );
            } else {
                $shop = StoreShop::run(
                    tenant:      app('currentTenant'),
                    modelData:   $shopData['shop'],
                    addressData: []
                );
            }


            foreach (
                DB::connection('aurora')
                    ->table('Category Dimension')
                    ->select('Category Key as source_id')
                    ->where('Category Branch Type', 'Head')
                    ->where('Category Root Key', $shopData['source_department_key'])->get() as $auroraDepartment
            ) {
                FetchDepartments::run($tenantSource, $auroraDepartment->source_id);
                $this->progressBar?->advance();
            }

            foreach (
                DB::connection('aurora')
                    ->table('Category Dimension')
                    ->select('Category Key as source_id')
                    ->where('Category Branch Type', 'Head')
                    ->where('Category Root Key', $shopData['source_family_key'])->get() as $auroraFamily

            ) {
                FetchFamilies::run($tenantSource, $auroraFamily->source_id);
                $this->progressBar?->advance();
            }

            return $shop;
        }

        return null;
    }

    public function fetchAll(SourceTenantService $tenantSource, Command $command = null): void
    {
        foreach ($this->getModelsQuery()->get() as $auroraData) {
            $this->handle($tenantSource, $auroraData->{'source_id'});
        }
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Store Dimension')
            ->select('Store Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        $count = 0;


        foreach (
            DB::connection('aurora')
                ->table('Store Dimension')->get() as $storeData

        ) {
            $count += DB::connection('aurora')
                ->table('Category Dimension')
                ->where('Category Branch Type', 'Head')
                ->where('Category Root Key', $storeData->{'Store Department Category Key'})
                ->count();

            $count += DB::connection('aurora')
                ->table('Category Dimension')
                ->where('Category Branch Type', 'Head')
                ->where('Category Root Key', $storeData->{'Store Family Category Key'})
                ->count();
        }

        return $count;
    }
}
