<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Id
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\InertiaAction;
use App\Models\HumanResources\Employee;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemoveEmployee extends InertiaAction
{
    public function handle(Employee $employee): Employee
    {
        return $employee;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.edit");
    }

    public function asController(Employee $employee, ActionRequest $request): Employee
    {
        $this->initialisation($request);

        return $this->handle($employee);
    }


    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete Employee'),
            'text'        => __("This action will delete this Employee"),
            'route'       => $route
        ];
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function htmlResponse(Employee $employee, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete employee'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $employee
                ),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-inventory'],
                            'title' => __('employee')
                        ],
                    'title' => $employee->slug,
                    'actions'=>[
                        [
                            'type'=>'button',
                            'style'=>'cancel',
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $this->routeName),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ]
                ],
                'data'      => $this->getAction(
                    route:[
                        'name' => 'models.employee.delete',
                        'parameters' => array_values($this->originalParameters)
                    ]
                )
            ]
        );
    }


    public function getBreadcrumbs(Employee $employee): array
    {
        return ShowEmployee::make()->getBreadcrumbs($employee, suffix: '('.__('deleting').')');
    }
}
