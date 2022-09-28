<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:38:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */


namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Marketing\Product\StoreProduct;
use App\Actions\Marketing\Product\UpdateProduct;
use App\Models\Marketing\Product;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchProducts extends FetchAction
{

    public string $commandSignature = 'fetch:products {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Product
    {
        if ($productData = $tenantSource->fetchStock($tenantSourceId)) {
            if ($product = Product::where('source_id', $productData['stock']['source_id'])
                ->first()) {
                $product = UpdateProduct::run(
                    product:     $product,
                    modelData: $productData['stock'],
                );
            } else {
                $product = StoreProduct::run(
                    shop:      $productData['shop'],
                    modelData: $productData['stock']
                );
            }

            $tradeUnits= $tenantSource->fetchProductStocks($productData['stock']['source_id']);
            $product->tradeUnits()->sync($tradeUnits['product_stocks']);



            return $product;
        }


        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Product Dimension')
            ->select('Product ID as source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Product Dimension')->count();
    }

}
