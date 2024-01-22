<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 21:36:34 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Actions\Procurement\SupplierProduct\SyncSupplierProductTradeUnits;
use App\Actions\Procurement\SupplierProduct\UpdateSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchSupplierProducts extends FetchAction
{
    public string $commandSignature = 'fetch:supplier-products {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?SupplierProduct
    {

        if ($supplierProductData = $organisationSource->fetchSupplierProduct($organisationSourceId)) {




            print_r($supplierProductData['supplierProduct']);
            if ($baseSupplierProduct=SupplierProduct::withTrashed()
                ->where(
                    'source_slug',
                    $supplierProductData['supplierProduct']['source_slug']
                )
                ->first()) {


                if($supplierProduct = SupplierProduct::withTrashed()->where('source_id', $supplierProductData['supplierProduct']['source_id'])
                    ->first()) {
                    $supplierProduct = UpdateSupplierProduct::make()->action(
                        supplierProduct: $supplierProduct,
                        modelData:       $supplierProductData['supplierProduct'],
                        skipHistoric:    true,
                        hydratorsDelay: $this->hydrateDelay
                    );
                }

            } else {

                $supplierProduct = StoreSupplierProduct::make()->action(
                    supplier: $supplierProductData['supplier'],
                    modelData: $supplierProductData['supplierProduct'],
                    skipHistoric: true,
                    hydratorsDelay: $this->hydrateDelay
                );
            }


            $tradeUnit = $supplierProductData['trade_unit'];



            if($supplierProduct) {
                SyncSupplierProductTradeUnits::run($supplierProduct, [
                    $tradeUnit->id => [
                        'package_quantity' => $supplierProductData['supplierProduct']['units_per_pack']
                    ]
                ]);
            } else {
                print_r($baseSupplierProduct);
                dd('errrrorrrr');
            }




            return $supplierProduct;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Supplier Part Dimension as spp')
            ->select('Supplier Part Key as source_id')
            ->where('spp.aiku_ignore', 'No')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')
            ->table('Supplier Part Dimension as spp')
            ->select('Supplier Part Key as source_id')
            ->where('spp.aiku_ignore', 'No')
            ->count();
    }
}
