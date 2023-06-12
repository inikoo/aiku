<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 22:01:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\WorkingPlace;

use App\Actions\HumanResources\WorkingPlace\Hydrators\WorkingPlaceHydrateUniversalSearch;
use App\Models\HumanResources\Workplace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreWorkingPlace
{
    use AsAction;
    use WithAttributes;

    public function handle(array $modelData): Workplace
    {

        $workplace               = Workplace::create($modelData);
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
        ];
    }

    public function asController(ActionRequest $request): Workplace
    {

        //dd($request->all());
        $request->validate();

        return $this->handle($request->validated());
    }

    public function htmlResponse(Workplace $workplace): RedirectResponse
    {
        return Redirect::route('hr.working-places.index', $workplace->slug);
    }
}
