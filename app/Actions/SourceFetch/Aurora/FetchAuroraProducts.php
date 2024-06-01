<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 02 Sept 2022 14:38:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Catalogue\Outer\StoreOuter;
use App\Actions\Catalogue\Billable\SetProductMainOuter;
use App\Actions\Catalogue\Billable\StorePhysicalGood;
use App\Actions\Catalogue\Billable\UpdatePhysicalGood;
use App\Actions\Studio\Media\SaveModelImages;
use App\Models\Catalogue\Outer;
use App\Models\Catalogue\Billable;
use App\Services\Organisation\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraProducts extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:products {organisations?*} {--s|source_id=} {--S|shop= : Shop slug} {--N|only_new : Fetch only new}  {--d|db_suffix=} {--r|reset}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Billable
    {
        if ($productData = $organisationSource->fetchProduct($organisationSourceId)) {
            $sourceData = explode(':', $productData['product']['source_id']);
            $tradeUnits = $organisationSource->fetchProductStocks($sourceData[1])['trade_units'];

            data_set(
                $productData,
                'product.trade_units',
                $tradeUnits
            );

            if ($product = Billable::withTrashed()->where('source_id', $productData['product']['source_id'])
                ->first()) {
                if (!$product->mainOuterable) {
                    print "fix missing main outerable\n";

                    $outer = Outer::where('historic_source_id', $productData['product']['historic_source_id'])->first();


                    if (!$outer) {
                        print "adding missing outer\n";

                        $outer = StoreOuter::run(
                            product: $product,
                            modelData: [
                                'code'               => $product->code,
                                'price'              => $productData['product']['main_outerable_price'],
                                'name'               => $product->name,
                                'is_main'            => true,
                                'main_outer_ratio'   => 1,
                                'source_id'          => $product->source_id,
                                'historic_source_id' => $product->historic_source_id
                            ]
                        );
                    }

                    SetProductMainOuter::run(
                        product: $product,
                        mainOuter: $outer
                    );

                    $product->refresh();
                }


                try {
                    $product = UpdatePhysicalGood::make()->action(
                        product: $product,
                        modelData: $productData['product'],
                    );
                } catch (Exception $e) {
                    dd($e);
                    $this->recordError($organisationSource, $e, $productData['product'], 'Billable', 'update');
                    return null;
                }
            } else {

                try {
                    $product = StorePhysicalGood::make()->action(
                        parent: $productData['parent'],
                        modelData: $productData['product'],
                        strict: false
                    );
                } catch (Exception $e) {
                    dd($e);
                    $this->recordError($organisationSource, $e, $productData['product'], 'Billable', 'store');

                    return null;
                }
            }


            $sourceData = explode(':', $product->source_id);

            DB::connection('aurora')->table('Product Dimension')
                ->where('Product ID', $sourceData[1])
                ->update(['aiku_id' => $product->id]);


            if(count($productData['product']['images'])>0) {
                foreach($productData['product']['images'] as $imageData) {
                    if (isset($imageData['image_path']) and isset($imageData['filename'])) {
                        // try {
                        SaveModelImages::run(
                            $product,
                            [
                                'path'         => $imageData['image_path'],
                                'originalName' => $imageData['filename'],

                            ],
                            'photo',
                            'product_images'
                        );
                        //} catch (Exception) {
                        //
                        // }
                    }
                }
            }
            return $product;
        }


        return null;
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Product Dimension')
            ->where('Product Type', 'Product')
            ->whereNull('Product Customer Key')
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
            ->whereNull('Product Customer Key')
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
