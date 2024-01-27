<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 May 2023 12:12:23 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Inventory\Location;

use App\Http\Resources\Inventory\LocationResource;
use App\Models\Inventory\Location;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsObject;
use App\Services\QueryBuilder;

class GetLocations
{
    use AsObject;
    use AsAction;

    public function handle()
    {
        return QueryBuilder::for(Location::class)
            ->select('id', 'slug', 'code')
            ->groupBy('locations.id')
            ->defaultSort('slug')
            ->allowedFilters(['slug'])
            ->jsonPaginate();
    }

    public function asController(ActionRequest $request): AnonymousResourceCollection
    {
        $locations = $this->handle();

        return LocationResource::collection($locations);
    }
}
