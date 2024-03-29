<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 30 Aug 2022 12:46:32 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia F
 */

namespace App\Actions\HumanResources\Clocking;

use App\Actions\OrgAction;
use App\Enums\HumanResources\Clocking\ClockingTypeEnum;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class StoreClocking extends OrgAction
{
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
        return $request->user()->hasPermissionTo("human-resources.workplaces.{$this->organisation->id}.edit");
    }

    public function rules(): array
    {
        return [
            'generator_id'         => ['required'],
        ];
    }

    public function asController(Organisation $organisation, ClockingMachine|Workplace $parent, ActionRequest $request): Clocking
    {
        $request->validate();

        return $this->handle($parent, $request->validated());
    }

    public function inClockingMachine(Organisation $organisation, ClockingMachine $clockingMachine, ActionRequest $request): Clocking
    {
        $request->validate();

        return $this->handle($clockingMachine, $request->validated());
    }

    public function htmlResponse(Clocking $clocking): RedirectResponse
    {
        if(!$clocking->clocking_machine_id) {
            return Redirect::route('grp.org.hr.workplaces.show.clockings.show', [
                $clocking->workplace->slug,
                $clocking->slug
            ]);
        } else {
            return Redirect::route('grp.org.hr.workplaces.show.clocking-machines.show.clockings.show', [
                $clocking->workplace->slug,
                $clocking->clockingMachine->slug,
                $clocking->slug
            ]);
        }
    }

    public function action(ClockingMachine|Workplace $parent, array $modelData): Clocking
    {
        $this->asAction=true;
        $this->setRawAttributes($modelData);
        $validatedData = $this->validateAttributes();

        return $this->handle($parent, $validatedData);
    }
}
