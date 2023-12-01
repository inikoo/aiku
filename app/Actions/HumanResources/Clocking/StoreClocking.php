<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Clocking;

use App\Enums\HumanResources\Clocking\ClockingTypeEnum;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\WithAttributes;

class StoreClocking
{
    use AsAction;
    use WithAttributes;

    private bool $asAction=false;

    public function handle(ClockingMachine|Workplace $parent, array $modelData): Clocking
    {

        if (class_basename($parent::class) == 'ClockingMachine') {
            $modelData['workplace_id'] = $parent->workplace_id;
        } else {
            $modelData['workplace_id'] = $parent->id;
        }
        $modelData['clocked_at'] = date('Y-m-d H:i:s');
        $modelData['type']       = ClockingTypeEnum::MANUAL;

        /** @var Clocking $clocking */
        $clocking = $parent->clockings()->create($modelData);

        return $clocking;
    }

    public function authorize(ActionRequest $request): bool
    {
        if($this->asAction) {
            return true;
        }
        return $request->user()->hasPermissionTo("hr.working-places.edit");
    }
    public function rules(): array
    {
        return [
            'generator_id'         => ['required'],
        ];
    }

    public function asController(ClockingMachine|Workplace $parent, ActionRequest $request): Clocking
    {
        $request->validate();

        return $this->handle($parent, $request->validated());
    }


    public function inClockingMachine(ClockingMachine $clockingMachine, ActionRequest $request): Clocking
    {
        $request->validate();

        return $this->handle($clockingMachine, $request->validated());
    }

    public function htmlResponse(Clocking $clocking): RedirectResponse
    {
        if(!$clocking->clocking_machine_id) {
            return Redirect::route('grp.hr.working-places.show.clockings.show', [
                $clocking->workplace->slug,
                $clocking->slug
            ]);
        } else {
            return Redirect::route('grp.hr.working-places.show.clocking-machines.show.clockings.show', [
                $clocking->workplace->slug,
                $clocking->clockingMachine->slug,
                $clocking->slug
            ]);
        }
    }

    public function action(ClockingMachine|Workplace $parent, array $objectData): Clocking
    {
        $this->asAction=true;
        $this->setRawAttributes($objectData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }
}
