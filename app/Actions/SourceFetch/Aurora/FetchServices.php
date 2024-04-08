<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Dec 2022 17:28:37 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Market\Product\StorePhysicalGood;
use App\Actions\Market\Product\UpdateProduct;
use App\Models\Market\HistoricProduct;
use App\Models\Market\Product;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;
use App\Services\Organisation\SourceOrganisationService;

class FetchServices extends FetchAction
{
    public string $commandSignature = 'fetch:services {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?Product
    {
        if ($productData = $organisationSource->fetchProduct($organisationSourceId)) {
            if ($product = Product::where('source_id', $productData['product']['source_id'])
                ->first()) {
                $product = UpdateProduct::run(
                    product:      $product,
                    modelData:    $productData['product'],
                    skipHistoric: true
                );
            } else {
                try {

                    $product = StorePhysicalGood::make()->action(
                        parent:         $serviceData['shop'],
                        modelData:    $serviceData['service'],
                        skipHistoric: true
                    );
                } catch (Exception $e) {
                    $this->recordError($organisationSource, $e, $serviceData['service'], 'Product', 'store');
                    return null;
                }
            }


            $historicProduct = HistoricProduct::where('source_id', $productData['historic_product_source_id'])->first();

            if (!$historicProduct) {
                $historicProduct = FetchHistoricProducts::run($organisationSource, $productData['historic_product_source_id']);
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
