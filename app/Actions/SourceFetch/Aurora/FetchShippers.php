<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 02:12:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Dispatch\Shipper\StoreShipper;
use App\Actions\Dispatch\Shipper\UpdateShipper;
use App\Models\Dispatch\Shipper;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchShippers extends FetchAction
{
    public string $commandSignature = 'fetch:shippers {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Shipper
    {
        if ($shipperData = $tenantSource->fetchShipper($tenantSourceId)) {
            if ($shipper = Shipper::where('source_id', $shipperData['shipper']['source_id'])
                ->first()) {
                $shipper = UpdateShipper::run(
                    shipper:   $shipper,
                    modelData: $shipperData['shipper']
                );
            } else {
                $shipper = StoreShipper::run(
                    modelData: $shipperData['shipper'],
                );
            }


            return $shipper;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Shipper Dimension')
            ->select('Shipper Key as source_id')
            ->where('Shipper Active', 'Yes')
            ->orderBy('source_id');
    }


    public function count(): ?int
    {
        return DB::connection('aurora')->table('Shipper Dimension')
            ->where('Shipper Active', 'Yes')
            ->count();
    }
}
