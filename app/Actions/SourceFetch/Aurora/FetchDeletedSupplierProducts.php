<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 17 Feb 2023 12:46:37 Malaysia Time, Bali
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;


use App\Actions\Procurement\SupplierProduct\StoreSupplierProduct;
use App\Actions\Procurement\SupplierProduct\UpdateSupplierProduct;
use App\Models\Procurement\SupplierProduct;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchDeletedSupplierProducts extends FetchAction
{

    public string $commandSignature = 'fetch:deleted-supplier-products {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?SupplierProduct
    {
        if ($supplierDeletedProductData = $tenantSource->fetchDeletedSupplierProduct($tenantSourceId)) {
            if (!empty($supplierDeletedProductData['supplierProduct'])) {
                if ($supplierProduct = SupplierProduct::withTrashed()->where('source_id', $supplierDeletedProductData['supplierProduct']['source_id'])
                    ->first()) {
                    $supplierProduct = UpdateSupplierProduct::run(
                        supplierProduct: $supplierProduct,
                        modelData:       $supplierDeletedProductData['supplierProduct'],
                        skipHistoric:    true

                    );
                } else {
                    $supplierProduct = StoreSupplierProduct::run(
                        supplier:     $supplierDeletedProductData['supplier'],
                        modelData:    $supplierDeletedProductData['supplierProduct'],
                        skipHistoric: true
                    );
                }

                return $supplierProduct;
            }
        }
        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Supplier Part Deleted Dimension')
            ->select('Supplier Part Deleted Key as source_id')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')
            ->table('Supplier Part Deleted Dimension')
            ->select('Supplier Part Deleted Key as source_id')
            ->count();
    }

}
