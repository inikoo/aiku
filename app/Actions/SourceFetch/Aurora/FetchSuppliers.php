<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 25 Oct 2022 10:26:55 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
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


class FetchSuppliers extends FetchAction
{

    public string $commandSignature = 'fetch:suppliers {tenants?*} {--s|source_id=}';


    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Supplier
    {
        if ($supplierData = $tenantSource->fetchSupplier($tenantSourceId)) {
            if ($supplier = Supplier::withTrashed()->where('source_id', $supplierData['supplier']['source_id'])
                ->first()) {
                $supplier = UpdateSupplier::run($supplier, $supplierData['supplier']);

                UpdateAddress::run($supplier->getAddress('contact'), $supplierData['address']);
                $supplier->location = $supplier->getLocation();
                $supplier->save();
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
