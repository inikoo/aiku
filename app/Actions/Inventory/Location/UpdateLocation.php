<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:05:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\Inventory\Location;

use App\Actions\OrgAction;
use App\Actions\Inventory\Location\Hydrators\LocationHydrateUniversalSearch;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\Inventory\LocationResource;
use App\Models\Inventory\Location;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateLocation extends OrgAction
{
    use WithActionUpdate;

    private bool $asAction = false;

    private Location $location;

    public function handle(Location $location, array $modelData): Location
    {
        $location = $this->update($location, $modelData, ['data']);

        LocationHydrateUniversalSearch::dispatch($location);
        HydrateLocation::run($location);

        return $location;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("inventory.locations.edit");
    }

    public function rules(): array
    {
        return [
            'code' => [
                'sometimes',
                'required',
                'max:64',
                'alpha_dash',
                new IUnique(
                    table: 'locations',
                    extraConditions: [
                        ['column' => 'warehouse_id', 'value' => $this->location->warehouse_id],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->location->id
                        ]
                    ]
                ),
            ],
        ];
    }

    public function action(Location $location, array $modelData): Location
    {
        $this->asAction = true;
        $this->location = $location;
        $this->initialisation($location->organisation, $modelData);

        return $this->handle($location, $this->validatedData);
    }

    public function asController(Location $location, ActionRequest $request): Location
    {
        $this->asAction = true;
        $this->location = $location;

        $this->initialisation($location->organisation, $request);

        return $this->handle($location, $this->validatedData);
    }


    public function jsonResponse(Location $location): LocationResource
    {
        return new LocationResource($location);
    }
}
