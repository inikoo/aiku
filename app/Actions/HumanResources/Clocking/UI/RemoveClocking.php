<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Clocking\UI;

use App\Actions\OrgAction;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveClocking extends OrgAction
{
    public function handle(Clocking $clocking): Clocking
    {
        return $clocking;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("human-resources.{$this->organisation->id}.edit");
    }

    public function asController(Organisation $organisation, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($organisation, $request);

        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWorkplace(Organisation $organisation, Workplace $workplace, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($organisation, $request);

        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inClockingMachine(Organisation $organisation, ClockingMachine $clockingMachine, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($organisation, $request);

        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWorkplaceInClockingMachine(Organisation $organisation, Workplace $workplace, ClockingMachine $clockingMachine, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($organisation, $request);

        return $this->handle($clocking);
    }


    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete Clocking'),
            'text'        => __("This action will delete this Clocking"),
            'route'       => $route
        ];
    }

    public function htmlResponse(Clocking $clocking, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete clocking'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-clock'],
                            'title' => __('clocking')
                        ],
                    'title'  => $clocking->slug,
                    'actions'=> [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $request->route()->getName()),
                                'parameters' => $request->route()->originalParameters()
                            ]
                        ]
                    ]
                ],
                'data'     => $this->getAction(
                    route:
                    match ($request->route()->getName()) {
                        'grp.org.hr.clockings.remove' => [
                            'name'       => 'grp.models.clocking.delete',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'grp.org.hr.workplaces.show.clockings.remove' => [
                            'name'       => 'grp.models.workplace.clocking.delete',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'grp.org.hr.clocking_machines.show.clockings.remove' => [
                            'name'       => 'grp.models.clocking-machine.clocking.delete',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'grp.org.hr.workplaces.show.clocking_machines.show.clockings.remove' => [
                            'name'       => 'grp.models.workplace.clocking-machine.clocking.delete',
                            'parameters' => $request->route()->originalParameters()
                        ]
                    }
                )
            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowClocking::make()->getBreadcrumbs(
            $routeName,
            routeParameters: $routeParameters,
            suffix: '('.__('deleting').')'
        );
    }
}
