<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\HumanResources\Clocking\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\LocationTabsEnum;
use App\Models\HumanResources\Clocking;
use App\Models\HumanResources\ClockingMachine;
use App\Models\HumanResources\Workplace;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditClocking extends InertiaAction
{
    public function handle(Clocking $clocking): Clocking
    {
        return $clocking;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('hr.edit');
        return $request->user()->hasPermissionTo("hr.view");
    }

    public function inTenant(Clocking $clocking, ActionRequest $request): Clocking
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
        $this->initialisation($request)->withTab(LocationTabsEnum::values());
        return $this->handle($clocking);
    }

    public function htmlResponse(Clocking $clocking, ActionRequest $request): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('clocking'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'pageHead' => [
                    'title'    => $clocking->slug,
                    'actions'  => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ]
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
                                'code' => [
                                    'type'  => 'input',
                                    'label' => __('code'),
                                    'value' => $clocking->slug
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'       => 'grp.models.clocking.update',
                            'parameters' => $clocking->slug

                        ],
                    ]
                ]
            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowClocking::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '(' . __('editing') . ')'
        );
    }
}
