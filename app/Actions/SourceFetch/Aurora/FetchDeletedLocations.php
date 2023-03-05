<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 20 Feb 2023 09:53:03 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\SourceFetch\Aurora;

use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Inventory\Location\UpdateLocation;
use App\Models\Inventory\Location;
use App\Services\Tenant\SourceTenantService;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchDeletedLocations extends FetchAction
{
    public string $commandSignature = 'fetch:deleted-locations {tenants?*} {--s|source_id=}';

    #[NoReturn] public function handle(SourceTenantService $tenantSource, int $tenantSourceId): ?Location
    {
        if ($deletedLocationData = $tenantSource->fetchDeletedLocation($tenantSourceId)) {
            if ($location = Location::withTrashed()->where('source_id', $deletedLocationData['location']['source_id'])
                ->first()) {
                $location = UpdateLocation::run(
                    location:  $location,
                    modelData: $deletedLocationData['location']
                );
            } else {
                $location = StoreLocation::run(
                    parent:    $deletedLocationData['parent'],
                    modelData: $deletedLocationData['location'],
                );
            }
            return $location;
        }

        return null;
    }

    public function getModelsQuery(): Builder
    {
        return DB::connection('aurora')
            ->table('Location Deleted Dimension')
            ->select('Location Deleted Key as source_id')
            ->orderBy('source_id')
            ->when(app()->environment('testing'), function ($query) {
                return $query->limit(20);
            });
    }


    public function count(): ?int
    {
        return DB::connection('aurora')->table('Location Deleted Dimension')->count();
    }
}
