<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\WorkingPlace;

use App\Actions\HumanResources\WorkingPlace\Hydrators\WorkingPlaceHydrateUniversalSearch;
use App\Actions\WithActionUpdate;
use App\Http\Resources\HumanResources\WorkPlaceResource;
use App\Models\HumanResources\Workplace;
use Lorisleiva\Actions\ActionRequest;

class UpdateWorkingPlace
{
    use WithActionUpdate;

    public function handle(Workplace $workplace, array $modelData): Workplace
    {
        $workplace =  $this->update($workplace, $modelData, ['data']);

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
            'name'      => ['required'],
            'type'      => ['required'],
            'owner_id'  => ['numeric','required'],
            'owner_type'=> ['required']
        ];
    }

    public function asController(Workplace $workplace, ActionRequest $request): Workplace
    {
        $request->validate();

        return $this->handle($workplace, $request->all());
    }


    public function jsonResponse(Workplace $workplace): WorkPlaceResource
    {
        return new WorkPlaceResource($workplace);
    }
}
