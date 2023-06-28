<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\ClockingMachine\UI;

use App\Actions\InertiaAction;
use App\Models\HumanResources\ClockingMachine;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveClockingMachine extends InertiaAction
{
    public function handle(ClockingMachine $clockingMachine): ClockingMachine
    {
        return $clockingMachine;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('hr.edit');
        return $request->user()->hasPermissionTo("hr.view");
    }

    public function asController(ClockingMachine $clockingMachine, ActionRequest $request): ClockingMachine
    {
        $this->initialisation($request);

        return $this->handle($clockingMachine);
    }


    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete Clocking Machine'),
            'text'        => __("This action will delete this Clocking Machine and its all Clocking"),
            'route'       => $route
        ];
    }

    public function htmlResponse(ClockingMachine $clockingMachine, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete clocking machine'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $clockingMachine,
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-chess-clock'],
                            'title' => __('clocking machine')
                        ],
                    'title' => $clockingMachine->slug,
                    'actions'=>[
                        [
                            'type'=>'button',
                            'style'=>'cancel',
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $this->routeName),
                                'parameters' => $clockingMachine->slug
                            ]
                        ]
                    ]
                ],
                'data'      => $this->getAction(
                    route:[
                        'name' => 'models.clocking-machine.delete',
                        'parameters' => array_values($this->originalParameters)
                    ]
                )
            ]
        );
    }


    public function getBreadcrumbs(ClockingMachine $clockingMachine, array $routeParameters): array
    {
        return ShowClockingMachine::make()->getBreadcrumbs($clockingMachine, $routeParameters, suffix: '('.__('deleting').')');
    }
}
