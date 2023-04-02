<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:38:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Marketing\Product\StoreProduct;
use App\Actions\Marketing\Product\UpdateProduct;
use App\Models\Marketing\HistoricProduct;
use App\Models\Marketing\Product;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchProducts extends FetchAction
{
    public string $commandSignature = 'fetch:products {tenants?*} {--s|source_id=} {--S|shop= : Shop slug}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Product
    {
        if ($productData = $tenantSource->fetchProduct($tenantSourceId)) {
            if ($product = Product::withTrashed()->where('source_id', $productData['product']['source_id'])
                ->first()) {
                $product = UpdateProduct::run(
                    product: $product,
                    modelData: $productData['product'],
                    skipHistoric: true
                );
            } else {
                $product = StoreProduct::run(
                    shop: $productData['shop'],
                    modelData: $productData['product'],
                    skipHistoric: true
                );
            }


            DB::connection('aurora')->table('Product Dimension')
                ->where('Product ID', $product->source_id)
                ->update(['aiku_id'=> $product->id]);


            $historicProduct = HistoricProduct::where('source_id', $productData['historic_product_source_id'])->first();
            if (!$historicProduct) {
                $historicProduct = FetchHistoricProducts::run($tenantSource, $productData['historic_product_source_id']);
            }

            $product->update(
                [
                    'current_historic_product_id' => $historicProduct->id
                ]
            );


            $tradeUnits = $tenantSource->fetchProductStocks($productData['product']['source_id']);


            $product->tradeUnits()->sync($tradeUnits['product_stocks']);



            return $product;
        }


        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product Type', 'Product')
            ->select('Product ID as source_id')
            ->orderBy('Product ID');

        if ($this->shop) {
            $query->where('Product Store Key', $this->shop->source_id);
        }

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Product Dimension')->where('Product Type', 'Product');
        if ($this->shop) {
            $query->where('Product Store Key', $this->shop->source_id);
        }

        return $query->count();
    }
}
