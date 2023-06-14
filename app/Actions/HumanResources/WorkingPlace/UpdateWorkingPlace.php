<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\WorkingPlace;

use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\HumanResources\WorkingPlace\Hydrators\WorkingPlaceHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\HumanResources\WorkPlaceResource;
use App\Models\HumanResources\Workplace;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\ActionRequest;

class UpdateWorkingPlace
{
    use WithActionUpdate;

    public function handle(Workplace $workplace, array $modelData, array $addressData): Workplace
    {
        $workplace =  $this->update($workplace, $modelData, ['data']);

        if($addressData) {
            StoreAddressAttachToModel::run($workplace, $addressData, ['scope' => 'contact']);

            $workplace->location = $workplace->getLocation();
            $workplace->save();
        }

        WorkingPlaceHydrateUniversalSearch::dispatch($workplace);
        return $workplace;
    }


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.edit");
    }

    public function rules(): array
    {
        return [
            'name'       => ['sometimes','required', 'max:255'],
            'type'       => ['sometimes','required'],
            'address'    => ['sometimes','required']
        ];
    }

    public function asController(Workplace $workplace, ActionRequest $request): Workplace
    {
        $request->validate();
        $validated=$request->validated();
        if (array_key_exists('address', $validated)) {
            return $this->handle(
                $workplace,
                modelData: Arr::except($validated, 'address'),
                addressData: Arr::only($validated, 'address')['address']
            );
        } else {
            return $this->handle($workplace, modelData: $validated, addressData: []);
        }
    }


    public function jsonResponse(Workplace $workplace): WorkPlaceResource
    {
        return new WorkPlaceResource($workplace);
    }
}
