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
use App\Models\Procurement\SupplierProduct;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchSupplierProducts extends FetchAction
{
    public string $commandSignature = 'fetch:supplier-products {tenants?*} {--s|source_id=} {--d|db_suffix=}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?SupplierProduct
    {
        if ($supplierProductData = $organisationSource->fetchSupplierProduct($organisationSourceId)) {



            $supplier=$supplierProductData['supplier'];


            if(!$supplier) {
                return null;
            }

            if($supplier->type=='supplier') {
                if($supplier->owner_id!=app('currentTenant')->id) {
                    return null;
                }
            } else {

                if(!$supplier->owner) {
                    return null;
                }

                if($supplier->owner->owner_id!=app('currentTenant')->id) {
                    return null;
                }

            }

            if ($supplierProduct = SupplierProduct::withTrashed()->where('source_id', $supplierProductData['supplierProduct']['source_id'])
                ->first()) {
                $supplierProduct = UpdateSupplierProduct::run(
                    supplierProduct: $supplierProduct,
                    modelData:       $supplierProductData['supplierProduct'],
                    skipHistoric:    true
                );
            } else {
                $supplierProduct = StoreSupplierProduct::make()->asFetch(
                    supplier: $supplierProductData['supplier'],
                    modelData: $supplierProductData['supplierProduct'],
                    hydratorsDelay: $this->hydrateDelay,
                    skipHistoric: true
                );
            }

            $tradeUnit = $supplierProductData['trade_unit'];

            SyncSupplierProductTradeUnits::run($supplierProduct, [
                $tradeUnit->id => [
                    'package_quantity' => $supplierProductData['supplierProduct']['units_per_pack']
                ]
            ]);

            return $supplierProduct;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Supplier Part Dimension')
            ->select('Supplier Part Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')
            ->table('Supplier Part Dimension')
            ->select('Supplier Part Key as source_id')
            ->count();
    }
}
