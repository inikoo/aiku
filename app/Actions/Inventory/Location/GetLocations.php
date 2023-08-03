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

class GetLocations
{
    use AsObject;
    use AsAction;

    public function handle(array $objectData = []): AnonymousResourceCollection
    {
        $query  = $objectData['query'];
        $users  = Location::where('slug', 'ILIKE', '%'.$query.'%')->get();

        return LocationResource::collection($users);
    }

    public function asController(ActionRequest $request): AnonymousResourceCollection
    {
        return $this->handle($request->all());
    }
}
