<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 22:01:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\ClockingMachine;

use App\Actions\HumanResources\ClockingMachine\Hydrators\ClockingMachineHydrateUniversalSearch;
use App\Actions\Tenancy\Tenant\Hydrators\TenantHydrateWorkingPlace;
use App\Models\ClockingMachine;
use App\Rules\CaseSensitive;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreClockingMachine
{
    use AsAction;
    use WithAttributes;

    public function handle(array $modelData): ClockingMachine
    {
        $modelData['workplace_id'] = 1;
        $clockingMachine           = ClockingMachine::create($modelData);
        TenantHydrateWorkingPlace::run(app('currentTenant'));
        ClockingMachineHydrateUniversalSearch::dispatch($clockingMachine);

        return $clockingMachine;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.edit");
    }


    public function rules(): array
    {
        return [
            'code'          => ['required', 'unique:tenant.clocking_machines', 'between:2,64', 'alpha_dash', new CaseSensitive('clocking_machines')],
            'workplace_id'  => ['sometimes','required'],
        ];
    }

    public function asController(ActionRequest $request): ClockingMachine
    {
        $request->validate();

        return $this->handle($request->validated());
    }

    public function htmlResponse(ClockingMachine $clockingMachine): RedirectResponse
    {
        return Redirect::route('hr.clocking-machines.show', $clockingMachine->slug);
    }
}
