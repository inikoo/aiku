<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 14:02:49 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\SourceUpserts\Aurora\Single;


use App\Actions\Inventory\Location\StoreLocation;
use App\Actions\Inventory\Location\UpdateLocation;
use App\Models\Inventory\Location;
use App\Services\Organisation\SourceOrganisationService;
use JetBrains\PhpStorm\NoReturn;
use Lorisleiva\Actions\Concerns\AsAction;


class UpsertLocationFromSource
{
    use AsAction;
    use WithSingleFromSourceCommand;

    public string $commandSignature = 'source-update:location {organisation_code} {organisation_source_id}';

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
            return $res->model;
        }

        return null;
    }


}
