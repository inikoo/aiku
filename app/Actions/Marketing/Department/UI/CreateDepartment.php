<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Department\UI;

use App\Actions\InertiaAction;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateDepartment extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('shops.departments.edit');
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function asController(Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->initialisation($request);

        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'       => __('new department'),
                'pageHead'    => [
                    'title'        => __('new department'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'shops.show.catalogue.hub.departments.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
                    ]

                ],
                'formData'    => [
                    'blueprint' =>
                        [

                        ],
                    'route'     => [
                        'name' => ''
                    ]
                ]


            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexDepartments::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'         => 'creatingModel',
                    'creatingModel'=> [
                        'label'=> __('creating department'),
                    ]
                ]
            ]
        );
    }
}
