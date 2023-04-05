<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Mar 2023 19:09:33 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\HumanResources\Employee\UI;

use App\Actions\InertiaAction;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateEmployee extends InertiaAction
{
    use HasUIEmployees;


    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new employee'),
                'pageHead'    => [
                    'title'        => __('new employee'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'hr.employees.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('personal information'),
                            'fields' => [

                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('name'),
                                    'value' => ''
                                ],
                                'date_of_birth' => [
                                    'type'  => 'date',
                                    'label' => __('date of birth'),
                                    'value' => ''
                                ],


                            ]
                        ]

                    ],
                    'route'      => [
                            'name'       => 'models.employee.store',

                    ]

                ],



            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('hr.edit');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }
}
