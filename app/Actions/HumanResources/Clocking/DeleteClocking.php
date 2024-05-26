<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Clocking;

use App\Actions\OrgAction;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;

class DeleteClocking extends OrgAction
{
    public function handle(Clocking $clocking): Clocking
    {
        $clocking->delete();

        return $clocking;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function asController(Organisation $organisation, Clocking $clocking, ActionRequest $request): Clocking
    {
        $request->validate();

        return $this->handle($clocking);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inWorkplace(Organisation $organisation, Workplace $workplace, Clocking $clocking, ActionRequest $request): Clocking
    {
        $request->validate();

        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inClockingMachine(Organisation $organisation, ClockingMachine $clockingMachine, Clocking $clocking, ActionRequest $request): Clocking
    {
        $request->validate();

        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWorkplaceInClockingMachine(Organisation $organisation, Workplace $workplace, ClockingMachine $clockingMachine, Clocking $clocking, ActionRequest $request): Clocking
    {
        $request->validate();

        return $this->handle($clocking);
    }


    public function htmlResponse(Workplace | ClockingMachine | Clocking $parent): RedirectResponse
    {
        if (class_basename($parent::class) == 'ClockingMachine') {
            return Redirect::route(
                route: 'grp.org.hr.workplace.show.clocking_machines.show.clockings.index',
                parameters: [
                    'organisation'      => $parent->organisation->slug,
                    'workplace'         => $parent->workplace->slug,
                    'clockingMachine'   => $parent->slug
                ]
            );
        } elseif (class_basename($parent::class) == 'Workplace') {
            return Redirect::route(
                route: 'grp.org.hr.clocking_machines.show.clockings.index',
                parameters: [
                    'organisation'      => $parent->organisation->slug,
                    'workplace'         => $parent->slug
                ]
            );
        } else {
            return Redirect::route('grp.org.hr.clockings.index');
        }
    }

}
