<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 13:12:05 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Clocking;

use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsController;
use Lorisleiva\Actions\Concerns\WithAttributes;

class DeleteClocking
{
    use AsController;
    use WithAttributes;

    public function handle(Clocking $clocking): Clocking
    {
        $clocking->delete();

        return $clocking;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->slug}.edit");
    }

    public function asController(Clocking $clocking, ActionRequest $request): Clocking
    {
        $request->validate();

        return $this->handle($clocking);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inWorkplace(Workplace $workplace, Clocking $clocking, ActionRequest $request): Clocking
    {
        $request->validate();

        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inClockingMachine(ClockingMachine $clockingMachine, Clocking $clocking, ActionRequest $request): Clocking
    {
        $request->validate();

        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWorkplaceInClockingMachine(Workplace $workplace, ClockingMachine $clockingMachine, Clocking $clocking, ActionRequest $request): Clocking
    {
        $request->validate();

        return $this->handle($clocking);
    }


    public function htmlResponse(Workplace | ClockingMachine | Clocking $parent): RedirectResponse
    {
        if (class_basename($parent::class) == 'ClockingMachine') {
            return Redirect::route(
                route: 'grp.org.hr.working-place.show.clocking-machines.show.clockings.index',
                parameters: [
                    'workplace'         => $parent->workplace->slug,
                    'clockingMachine'   => $parent->slug
                ]
            );
        } elseif (class_basename($parent::class) == 'Workplace') {
            return Redirect::route(
                route: 'grp.org.hr.clocking-machines.show.clockings.index',
                parameters: [
                    'workplace' => $parent->slug
                ]
            );
        } else {
            return Redirect::route('grp.org.hr.clockings.index');
        }
    }

}
