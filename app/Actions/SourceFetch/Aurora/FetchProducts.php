<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:38:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Market\Product\StoreProduct;
use App\Actions\Market\Product\SyncProductTradeUnits;
use App\Actions\Market\Product\UpdateProduct;
use App\Models\Market\HistoricProduct;
use App\Models\Market\Product;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchProducts extends FetchAction
{
    public string $commandSignature = 'fetch:products {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--N|only_new : Fetch only new}  {--d|db_suffix=} {--r|reset}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Product
    {
        if ($productData = $organisationSource->fetchProduct($organisationSourceId)) {
            //print_r($productData['product']);
            if ($product = Product::withTrashed()->where('source_id', $productData['product']['source_id'])
                ->first()) {
                $product = UpdateProduct::make()->action(
                    product: $product,
                    modelData: $productData['product'],
                    skipHistoric: true
                );
            } else {
                $product = StoreProduct::make()->action(
                    parent: $productData['parent'],
                    modelData: $productData['product'],
                    skipHistoric: true
                );
            }




            $sourceData = explode(':', $product->source_id);

            DB::connection('aurora')->table('Product Dimension')
                ->where('Product ID', $sourceData[1])
                ->update(['aiku_id' => $product->id]);


            $historicProduct = HistoricProduct::where('source_id', $productData['historic_product_source_id'])->first();
            if (!$historicProduct) {
                $historicProduct = FetchHistoricProducts::run($organisationSource, $productData['historic_product_source_id']);
            }

            $product->update(
                [
                    'current_historic_product_id' => $historicProduct->id
                ]
            );


            $tradeUnits = $organisationSource->fetchProductStocks($sourceData[1]);


            SyncProductTradeUnits::run($product, $tradeUnits['product_stocks']);


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

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $query->where('Product Store Key', $this->shop->source_id);
        }

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Product Dimension')->where('Product Type', 'Product');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $query->where('Product Store Key', $this->shop->source_id);
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Product Dimension')->update(['aiku_id' => null]);
    }
}
