<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 16 Feb 2023 12:18:36 Malaysia Time, Bali Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Helpers\Address\UpdateAddress;
use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Actions\Procurement\Supplier\UpdateSupplier;
use App\Models\Procurement\Supplier;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchDeletedSuppliers extends FetchAction
{
    public string $commandSignature = 'fetch:deleted-suppliers {tenants?*} {--s|source_id=} {--d|db_suffix=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Supplier
    {
        if ($deletedSupplierData = $tenantSource->fetchDeletedSupplier($tenantSourceId)) {
            if ($deletedSupplierData['supplier']) {
                if ($supplier = Supplier::withTrashed()->where('source_id', $deletedSupplierData['supplier']['source_id'])
                    ->first()) {
                    $supplier = UpdateSupplier::run(
                        supplier:  $supplier,
                        modelData: $deletedSupplierData['supplier'],
                    );
                    UpdateAddress::run($supplier->getAddress('contact'), $deletedSupplierData['address']);
                    $supplier->location = $supplier->getLocation();
                    $supplier->save();
                } else {
                    $supplier = StoreSupplier::run(
                        owner:       $deletedSupplierData['owner'],
                        modelData:   $deletedSupplierData['supplier'],
                        addressData: $deletedSupplierData['address']
                    );
                }

                DB::connection('aurora')->table('Supplier Deleted Dimension')
                    ->where('Supplier Deleted Key', $supplier->source_id)
                    ->update(['aiku_id' => $supplier->id]);

                return $supplier;
            }
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Supplier Deleted Dimension')
            ->select('Supplier Deleted Key as source_id')
            ->orderBy('source_id');
    }

    public function count(): ?int
    {
        return DB::connection('aurora')->table('Supplier Deleted Dimension')->count();
    }
}
