<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Market\ProductCategory\UI;

use App\Actions\InertiaAction;
use App\Models\Market\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateFamily extends InertiaAction
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

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->htmlResponse($request);
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
                'title'       => __('New Family'),
                'pageHead'    => [
                    'title'        => __('new family'),
                    'actions'      => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => 'shops.show.families.index',
                                'parameters' => array_values($this->originalParameters)
                            ],
                        ]
                    ]
                ],
                'formData'    => [
                    'blueprint' =>
                        [
                            [
                                'title'  => __('family'),
                                'fields' => [
                                    'code' => [
                                        'type'     => 'input',
                                        'label'    => __('code'),
                                        'required' => true
                                    ],
                                    'name' => [
                                        'type'     => 'input',
                                        'label'    => __('name'),
                                        'required' => true
                                    ],
                                ]
                            ]
                        ],
                    'route' => match ($this->routeName) {
                        'shops.show.families.create' => [
                            'name'      => 'models.shop.family.store',
                            'arguments' => [$request->route()->parameters['shop']->slug]
                        ],
                        default => [
                            'name' => 'models.family.store'
                        ]
                    }
                ]
            ]
        );
    }



    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexFamilies::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'         => 'creatingModel',
                    'creatingModel'=> [
                        'label'=> __('creating family'),
                    ]
                ]
            ]
        );
    }
}
