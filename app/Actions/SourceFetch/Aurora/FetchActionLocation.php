<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 01:11:27 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Inventory\Location\UpdateLocation;
use App\Models\Inventory\Location;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchActionLocation extends FetchAction
{


    public string $commandSignature = 'fetch:locations {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Location
    {
        if ($locationData = $tenantSource->fetchLocation($tenantSourceId)) {
            if ($location = Location::where('source_id', $locationData['location']['source_id'])
                ->first()) {
                $location = UpdateLocation::run(
                    location: $location,
                    modelData:     $locationData['location']
                );
            } else {
                $location = StoreLocation::run(
                    parent: $locationData['parent'],
                    modelData: $locationData['location'],
                );
            }
            $this->progressBar?->advance();

            return $location;
        }

        return null;
    }

    function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Location Dimension')
            ->select('Location Key as source_id')
            ->orderBy('source_id');
    }


    function count(): ?int
    {
        return DB::connection('aurora')->table('Location Dimension')->count();
    }

}
