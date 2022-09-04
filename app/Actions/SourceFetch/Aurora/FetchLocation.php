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
use App\Services\Organisation\SourceOrganisationService;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\NoReturn;

class FetchLocation extends FetchModel
{


    public string $commandSignature = 'fetch:locations {organisation_code} {organisation_source_id?}';

    #[NoReturn] public function handle(SourceOrganisationService $organisationSource, int $organisation_source_id): ?Location
    {
        if ($locationData = $organisationSource->fetchLocation($organisation_source_id)) {
            if ($location = Location::where('organisation_source_id', $locationData['location']['organisation_source_id'])
                ->where('organisation_id', $organisationSource->organisation->id)
                ->first()) {
                $res = UpdateLocation::run(
                    location: $location,
                    modelData:     $locationData['location']
                );
            } else {
                $res = StoreLocation::run(
                    parent: $locationData['parent'],
                    modelData: $locationData['location'],
                );
            }
            $this->progressBar?->advance();

            return $res->model;
        }

        return null;
    }

    function fetchAll(SourceOrganisationService $organisationSource): void
    {
        foreach (
            DB::connection('aurora')
                ->table('Location Dimension')
                ->select('Location Key')
                ->get() as $auroraData
        ) {
            $this->handle($organisationSource, $auroraData->{'Location Key'});
        }
    }

    function count(): ?int
    {
        return DB::connection('aurora')->table('Location Dimension')->count();
    }

}
