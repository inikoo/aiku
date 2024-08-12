<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 12:46:37 Malaysia Time, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\SupplyChain\SupplierProduct\StoreSupplierProduct;
use App\Actions\SupplyChain\SupplierProduct\UpdateSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use App\Transfers\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FetchAuroraDeletedSupplierProducts extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:deleted-supplier-products {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?SupplierProduct
    {
        if ($supplierDeletedProductData = $organisationSource->fetchDeletedSupplierProduct($organisationSourceId)) {
            if (!empty($supplierDeletedProductData['supplierProduct'])) {
                if ($supplierProduct = SupplierProduct::withTrashed()->where('source_id', $supplierDeletedProductData['supplierProduct']['source_id'])
                    ->first()) {
                    $supplierProduct = UpdateSupplierProduct::run(
                        supplierProduct: $supplierProduct,
                        modelData:       $supplierDeletedProductData['supplierProduct'],
                        skipHistoric:    true
                    );
                } else {
                    $supplierProduct = StoreSupplierProduct::make()->action(
                        supplier: $supplierDeletedProductData['supplier'],
                        modelData: $supplierDeletedProductData['supplierProduct'],
                        skipHistoric: true,
                        hydratorsDelay: $this->hydrateDelay,
                        strict: false
                    );
                }

                return $supplierProduct;
            }
        }
        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Supplier Part Deleted Dimension')
            ->select('Supplier Part Deleted Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')
            ->table('Supplier Part Deleted Dimension')
            ->select('Supplier Part Deleted Key as source_id')
            ->count();
    }
}
