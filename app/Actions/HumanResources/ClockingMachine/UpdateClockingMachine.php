<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Fri, 26 Aug 2022 00:49:45 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\ClockingMachine;

use App\Actions\HumanResources\ClockingMachine\Hydrators\ClockingMachineHydrateUniversalSearch;
use App\Actions\OrgAction;
use App\Actions\Traits\WithActionUpdate;
use App\Http\Resources\HumanResources\ClockingMachineResource;
use App\Models\HumanResources\ClockingMachine;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Lorisleiva\Actions\ActionRequest;

class UpdateClockingMachine extends OrgAction
{
    use WithActionUpdate;


    private ClockingMachine $clockingMachine;

    public function handle(ClockingMachine $clockingMachine, array $modelData): ClockingMachine
    {
        $clockingMachine = $this->update($clockingMachine, $modelData, ['data']);
        ClockingMachineHydrateUniversalSearch::dispatch($clockingMachine);

        return $clockingMachine;
    }


    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'required',
                'max:255',
                new IUnique(
                    table: 'clocking_machines',
                    extraConditions: [
                        [
                            'column' => 'organisation_id',
                            'value'  => $this->organisation->id,

                        ],
                        [
                            'column'   => 'id',
                            'operator' => '!=',
                            'value'    => $this->clockingMachine->id
                        ],
                    ]
                ),

            ],
            'source_id'  => 'sometimes|string|max:255',

        ];
    }

    public function asController(Organisation $organisation, ClockingMachine $clockingMachine, ActionRequest $request): ClockingMachine
    {
        $this->clockingMachine = $clockingMachine;
        $this->initialisation($organisation, $request);

        return $this->handle($clockingMachine, $this->validatedData);
    }

    public function action(ClockingMachine $clockingMachine, array $modelData): ClockingMachine
    {
        $this->asAction        =true;
        $this->clockingMachine = $clockingMachine;
        $this->initialisation($clockingMachine->organisation, $modelData);

        return $this->handle($clockingMachine, $this->validatedData);
    }

    public function jsonResponse(ClockingMachine $clockingMachine): ClockingMachineResource
    {
        return new ClockingMachineResource($clockingMachine);
    }
}
