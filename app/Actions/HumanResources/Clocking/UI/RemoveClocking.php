<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Clocking\UI;

use App\Actions\InertiaAction;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveClocking extends InertiaAction
{
    public function handle(Clocking $clocking): Clocking
    {
        return $clocking;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.edit");
    }

    public function asController(Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($request);

        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWorkplace(Workplace $workplace, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($request);

        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inClockingMachine(ClockingMachine $clockingMachine, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($request);

        return $this->handle($clocking);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inWorkplaceInClockingMachine(Workplace $workplace, ClockingMachine $clockingMachine, Clocking $clocking, ActionRequest $request): Clocking
    {
        $this->initialisation($request);

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
                    $request->route()->parameters
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
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ]
                ],
                'data'     => $this->getAction(
                    route:
                    match ($request->route()->getName()) {
                        'hr.clockings.remove' => [
                            'name'       => 'models.clocking.delete',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'hr.working-places.show.clockings.remove' => [
                            'name'       => 'models.working-place.clocking.delete',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'hr.clocking-machines.show.clockings.remove' => [
                            'name'       => 'models.clocking-machine.clocking.delete',
                            'parameters' => $request->route()->originalParameters()
                        ],
                        'hr.working-places.show.clocking-machines.show.clockings.remove' => [
                            'name'       => 'models.working-place.clocking-machine.clocking.delete',
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
