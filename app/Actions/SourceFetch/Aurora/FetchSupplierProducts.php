<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 21:36:34 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Procurement\Supplier\StoreSupplier;
use App\Actions\Procurement\Supplier\UpdateSupplier;
use App\Models\Procurement\Supplier;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;


class FetchSupplierProducts extends FetchAction
{

    public string $commandSignature = 'fetch:supplier-products {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Supplier
    {
        if ($supplierData = $tenantSource->fetchSupplier($tenantSourceId)) {
            if ($supplier = Supplier::withTrashed()->where('source_id', $supplierData['supplier']['source_id'])
                ->first()) {
                $supplier = UpdateSupplier::run($supplier, $supplierData['supplier']);
            } else {
                $supplier = StoreSupplier::run(
                    parent:      tenant(),
                    modelData:   $supplierData['supplier'],
                    addressData: $supplierData['address']
                );
            }

            return $supplier;
        }

        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Supplier Dimension')
            ->leftJoin('Agent Supplier Bridge', 'Agent Supplier Supplier Key', 'Supplier Key')
            ->select('Supplier Key as source_id')
            ->whereNull('Agent Supplier Agent Key')
            ->where('aiku_ignore', 'No')
            ->orderBy('source_id');
    }

    function count(): ?int
    {
        return DB::connection('aurora')
            ->table('Supplier Dimension')
            ->leftJoin('Agent Supplier Bridge', 'Agent Supplier Supplier Key', 'Supplier Key')
            ->whereNull('Agent Supplier Agent Key')
            ->where('aiku_ignore', 'No')
            ->count();
    }

}
