<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 13:05:43 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Clocking;

use App\Actions\HumanResources\Clocking\Hydrators\ClockingHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\Inventory\LocationResource;
use App\Models\HumanResources\Clocking;
use Lorisleiva\Actions\ActionRequest;

class UpdateClocking
{
    use WithActionUpdate;

    private bool $asAction=false;

    public function handle(Clocking $clocking, array $modelData): Clocking
    {
        $clocking =  $this->update($clocking, $modelData, ['data']);

        ClockingHydrateUniversalSearch::dispatch($clocking);
        //        HydrateClocking::run($clocking);

        return $clocking;
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
            'code'         => ['sometimes', 'required', 'unique:tenant.locations', 'between:2,64', 'alpha_dash'],
        ];
    }
    public function action(Clocking $clocking, array $objectData): Clocking
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($clocking, $validatedData);
    }

    public function asController(Clocking $clocking, ActionRequest $request): Clocking
    {
        $request->validate();
        return $this->handle($clocking, $request->all());
    }

    public function jsonResponse(Clocking $clocking): LocationResource
    {
        return new LocationResource($clocking);
    }
}
