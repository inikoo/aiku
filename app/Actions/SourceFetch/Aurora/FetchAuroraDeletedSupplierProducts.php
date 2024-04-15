<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 12:46:37 Malaysia Time, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Actions\Procurement\SupplierProduct\UpdateSupplierProduct;
use App\Models\SupplyChain\SupplierProduct;
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchAuroraDeletedSupplierProducts extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:deleted-supplier-products {organisations?*} {--s|source_id=} {--d|db_suffix=}';


    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?SupplierProduct
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
                    $supplierProduct = StoreSupplierProduct::make()->asFetch(
                        supplier:     $supplierDeletedProductData['supplier'],
                        modelData:    $supplierDeletedProductData['supplierProduct'],
                        hydratorsDelay: $this->hydrateDelay,
                        skipHistoric: true
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
