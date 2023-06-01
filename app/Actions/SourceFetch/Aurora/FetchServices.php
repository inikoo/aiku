<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 17:28:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Marketing\Product\StoreProduct;
use App\Actions\Marketing\Product\UpdateProduct;
use App\Models\Marketing\HistoricProduct;
use App\Models\Marketing\Product;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use App\Services\Tenant\SourceTenantService;

class FetchServices extends FetchAction
{
    public string $commandSignature = 'fetch:services {tenants?*} {--s|source_id=} {--d|db_suffix=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Product
    {
        if ($productData = $tenantSource->fetchProduct($tenantSourceId)) {
            if ($product = Product::where('source_id', $productData['product']['source_id'])
                ->first()) {
                $product = UpdateProduct::run(
                    product:      $product,
                    modelData:    $productData['product'],
                    skipHistoric: true
                );
            } else {
                $product = StoreProduct::run(
                    shop:         $productData['shop'],
                    modelData:    $productData['product'],
                    skipHistoric: true
                );
            }


            $historicProduct = HistoricProduct::where('source_id', $productData['historic_product_source_id'])->first();

            if (!$historicProduct) {
                $historicProduct = FetchHistoricProducts::run($tenantSource, $productData['historic_product_source_id']);
            }

            $product->update(
                [
                    'current_historic_product_id' => $historicProduct->id
                ]
            );

            return $product;
        }


        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product Type', 'Service')
            ->select('Product ID as source_id')
            ->orderBy('Product ID');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Product Dimension')->where('Product Type', 'Service')->count();
    }
}
