<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:05:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Location;

use App\Actions\Inventory\Location\Hydrators\LocationHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Inventory\LocationResource;
use App\Models\Inventory\Location;
use Lorisleiva\Actions\ActionRequest;

class UpdateLocation
{
    use WithActionUpdate;

    public function handle(Location $location, array $modelData): Location
    {
        $location =  $this->update($location, $modelData, ['data']);

        LocationHydrateUniversalSearch::dispatch($location);

        return $location;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.locations.edit");
    }
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required'],
            'name' => ['sometimes', 'required'],
        ];
    }


    public function asController(Location $location, ActionRequest $request): Location
    {
        $request->validate();
        return $this->handle($location, $request->all());
    }


    public function jsonResponse(Location $location): LocationResource
    {
        return new LocationResource($location);
    }
}
