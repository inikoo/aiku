<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 22:01:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\ClockingMachine;

use App\Actions\HumanResources\ClockingMachine\Hydrators\ClockingMachineHydrateUniversalSearch;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
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

    private bool $asAction = false;

    public function handle(Workplace $workplace, array $modelData): ClockingMachine
    {
        /** @var ClockingMachine $clockingMachine */
        $clockingMachine =  $workplace->clockingMachines()->create($modelData);
        ClockingMachineHydrateUniversalSearch::dispatch($clockingMachine);
        return $clockingMachine;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("hr.edit");
    }


    public function rules(): array
    {
        return [
            'code'  => ['required', 'unique:tenant.clocking_machines', 'between:2,64', 'alpha_dash', new CaseSensitive('clocking_machines')],
        ];
    }

    public function asController(Workplace $workplace, ActionRequest $request): ClockingMachine
    {
        $request->validate();

        return $this->handle($workplace, $request->validated());
    }

    public function htmlResponse(ClockingMachine $clockingMachine): RedirectResponse
    {
        return Redirect::route(
            'hr.working-places.show.clocking-machines.show',
            [
                'workplace'       => $clockingMachine->workplace->slug,
                'clockingMachine' => $clockingMachine->slug
            ]
        );
    }

    public function action(Workplace $workplace, array $objectData): ClockingMachine
    {
        $this->asAction = true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($workplace, $validatedData);
    }
}
