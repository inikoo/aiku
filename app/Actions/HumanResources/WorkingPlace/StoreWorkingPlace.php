<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 22:01:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\WorkingPlace;

use App\Actions\Helpers\Address\StoreAddressAttachToModel;
use App\Actions\HumanResources\WorkingPlace\Hydrators\WorkingPlaceHydrateUniversalSearch;
use App\Models\HumanResources\Workplace;
use App\Rules\ValidAddress;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;
use Illuminate\Support\Arr;

class StoreWorkingPlace
{
    use AsAction;
    use WithAttributes;

    public function handle(array $modelData, array $addressData): Workplace
    {
        $workplace               = Workplace::create($modelData);
        StoreAddressAttachToModel::run($workplace, $addressData, ['scope' => 'contact']);

        $workplace->location = $workplace->getLocation();
        $workplace->save();

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
            'name'       => ['required', 'max:255'],
            'type'       => ['required'],
            'address'    => ['required', new ValidAddress()]
        ];
    }

    public function asController(ActionRequest $request): Workplace
    {

        $request->validate();
        $validated=$request->validated();

        return $this->handle(
            modelData: Arr::except($validated, 'address'),
            addressData: Arr::only($validated, 'address')['address']
        );
    }

    public function htmlResponse(Workplace $workplace): RedirectResponse
    {
        return Redirect::route('hr.working-places.index', $workplace->slug);
    }
}
