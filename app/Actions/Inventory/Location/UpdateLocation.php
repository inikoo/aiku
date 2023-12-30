<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:05:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Location;

use App\Actions\Inventory\Location\Hydrators\LocationHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Inventory\LocationResource;
use App\Models\Inventory\Location;
use Lorisleiva\Actions\ActionRequest;

class UpdateLocation
{
    use WithActionUpdate;

    private bool $asAction=false;

    public function handle(Location $location, array $modelData): Location
    {
        $location =  $this->update($location, $modelData, ['data']);

        LocationHydrateUniversalSearch::dispatch($location);
        HydrateLocation::run($location);

        return $location;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("inventory.locations.edit");
    }
    public function rules(): array
    {
        return [
            'code'         => ['sometimes', 'required', 'unique:locations', 'between:2,64', 'alpha_dash'],
        ];
    }
    public function action(Location $location, array $modelData): Location
    {
        $this->asAction=true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($location, $validatedData);
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
