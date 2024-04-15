<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:38:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Market\Product\StorePhysicalGood;
use App\Actions\Market\Product\UpdateProduct;
use App\Models\Market\Product;
use App\Services\Organisation\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraProducts extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:products {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--N|only_new : Fetch only new}  {--d|db_suffix=} {--r|reset}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Product
    {
        if ($productData = $organisationSource->fetchProduct($organisationSourceId)) {

            $sourceData = explode(':', $productData['product']['source_id']);
            $tradeUnits =$organisationSource->fetchProductStocks($sourceData[1])['trade_units'];

            if(count($tradeUnits)==0) {
                return null;
            }

            data_set(
                $productData,
                'product.trade_units',
                $tradeUnits
            );


            if ($product = Product::withTrashed()->where('source_id', $productData['product']['source_id'])
                ->first()) {
                try {
                    $product = UpdateProduct::make()->action(
                        product: $product,
                        modelData: $productData['product'],
                        skipHistoric: true
                    );
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $productData['product'], 'Product', 'update');
                    return null;
                }
            } else {
                try {
                    $product = StorePhysicalGood::make()->action(
                        parent: $productData['parent'],
                        modelData: $productData['product'],
                        skipHistoric: true
                    );
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $productData['product'], 'Product', 'store');
                    return null;
                }
            }


            $sourceData = explode(':', $product->source_id);

            DB::connection('aurora')->table('Product Dimension')
                ->where('Product ID', $sourceData[1])
                ->update(['aiku_id' => $product->id]);





            return $product;
        }


        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product Type', 'Product')
            ->where('is_variant', 'No')
            ->select('Product ID as source_id')
            ->orderBy('Product ID');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Product Store Key', $sourceData[1]);
        }

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Product Dimension')
            ->where('is_variant', 'No')
            ->where('Product Type', 'Product');

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        if ($this->shop) {
            $sourceData = explode(':', $this->shop->source_id);
            $query->where('Product Store Key', $sourceData[1]);
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Product Dimension')->update(['aiku_id' => null]);
    }
}
