<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 25 Aug 2022 22:01:02 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\ClockingMachine;

use App\Actions\HumanResources\ClockingMachine\Hydrators\ClockingMachineHydrateUniversalSearch;
use App\Actions\HumanResources\Workplace\Hydrators\WorkplaceHydrateClockingMachines;
use App\Actions\OrgAction;
use App\Actions\SysAdmin\Organisation\Hydrators\OrganisationHydrateClockingMachines;
use App\Enums\HumanResources\ClockingMachine\ClockingMachineTypeEnum;
use App\Http\Resources\HumanResources\ClockingMachineResource;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use App\Rules\IUnique;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Lorisleiva\Actions\ActionRequest;

class StoreClockingMachine extends OrgAction
{
    public function handle(Workplace $workplace, array $modelData): ClockingMachine
    {
        data_set($modelData, 'group_id', $workplace->group_id);
        data_set($modelData, 'organisation_id', $workplace->organisation_id);

        if (Arr::get($modelData, 'type') == ClockingMachineTypeEnum::STATIC_NFC->value) {
            data_set($modelData, 'data.nfc_tag', $this->get('nfc_tag'));
            Arr::forget($modelData, 'nfc_tag');
        }


        /** @var ClockingMachine $clockingMachine */
        $clockingMachine =  $workplace->clockingMachines()->create($modelData);
        $clockingMachine->stats()->create();

        OrganisationHydrateClockingMachines::dispatch($workplace->organisation);
        WorkplaceHydrateClockingMachines::dispatch($workplace);

        ClockingMachineHydrateUniversalSearch::dispatch($clockingMachine);
        return $clockingMachine;
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->asAction) {
            return true;
        }

        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }


    public function rules(): array
    {
        return [
            'name'  => ['required', 'max:64', 'string',
                        new IUnique(
                            table: 'workplaces',
                            extraConditions: [
                                ['column' => 'group_id', 'value' => $this->organisation->group_id],
                            ]
                        ),
                ],
            'type'       => ['required', Rule::enum(ClockingMachineTypeEnum::class)],
            'nfc_tag'    => ['sometimes', 'string'],
            'source_id'  => 'sometimes|string|max:255',
            'created_at' => 'sometimes|date',
        ];
    }

    public function afterValidator(Validator $validator, ActionRequest $request): void
    {
        if ($this->get('type') == ClockingMachineTypeEnum::STATIC_NFC && empty($this->get('nfc_tag'))) {
            $validator->errors()->add('nfc_tag', 'Invalid NFC Tag');
        }
    }

    public function asController(Organisation $organisation, Workplace $workplace, ActionRequest $request): ClockingMachine
    {
        $this->initialisation($organisation, $request);
        return $this->handle($workplace, $this->validatedData);
    }

    public function action(Workplace $workplace, array $modelData): ClockingMachine
    {
        $this->asAction = true;
        $this->initialisation($workplace->organisation, $modelData);
        return $this->handle($workplace, $this->validatedData);
    }

    public function htmlResponse(ClockingMachine $clockingMachine): RedirectResponse
    {
        return Redirect::route(
            'grp.org.hr.workplaces.show.clocking-machines.show',
            [
                'organisation'    => $this->organisation->slug,
                'workplace'       => $clockingMachine->workplace->slug,
                'clockingMachine' => $clockingMachine->slug
            ]
        );
    }
    public function jsonResponse(ClockingMachine $clockingMachine): ClockingMachineResource
    {
        return ClockingMachineResource::make($clockingMachine);
    }
}
