<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:30 Central European Summer, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Web\Website\UI;

use App\Actions\InertiaAction;
use App\Actions\Market\Shop\UI\ShowShop;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateWebsite extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('shops.edit');
    }


    /**
     * @throws \Exception
     */
    public function inOrganisation(ActionRequest $request): Response|RedirectResponse
    {
        $this->initialisation($request);

        return $this->handle(app('currentTenant'), $request);
    }

    /**
     * @throws Exception
     */
    public function inShop(Shop $shop, ActionRequest $request): Response|RedirectResponse
    {
        $this->initialisation($request);
        if ($shop->website) {
            return Redirect::route('grp.org.shops.show.websites.show', [
                $shop->website->slug
            ]);
        }

        return $this->handle($shop, $request);
    }

    /**
     * @throws Exception
     */
    public function handle(Organisation|Shop $parent, ActionRequest $request): Response
    {
        $scope     = $parent;
        $container = null;
        if (class_basename($scope) == 'Shop') {
            $container = [
                'icon'    => ['fal', 'fa-store-alt'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($scope->name)
            ];
        }

        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $scope,
                    routeParameters: $request->route()->parameters
                ),
                'title'       => __('new website'),
                'pageHead'    => [
                    'title'        => __('website'),
                    'container'    => $container,
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'shops.show',
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                    ]

                ],
                'formData'    => [
                    'blueprint' => [
                        [
                            'title'  => __('domain'),
                            'fields' => [

                                'domain' => [
                                    'type'      => 'inputWithAddOn',
                                    'label'     => __('domain'),
                                    'leftAddOn' => [
                                        'label' => 'http://www.'
                                    ],
                                    'required'  => true,
                                ],


                            ]
                        ],
                        [
                            'title'  => __('ID/name'),
                            'fields' => [

                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'required' => true,
                                ],
                                'name' => [
                                    'type'     => 'input',
                                    'label'    => __('name'),
                                    'required' => true,
                                    'value'    => '',
                                ],


                            ]
                        ],


                    ],
                    'route'     =>
                        match (class_basename($scope)) {
                            'Shop' => [
                                'name'      => 'grp.models.shop.website.store',
                                'arguments' => [$parent->slug]
                            ],
                            'Organisation' => [
                                'name' => 'grp.models.website.store',
                            ],
                        }


                ],

            ]
        );
    }


    public function getBreadcrumbs(Organisation|Shop $scope, $routeParameters): array
    {
        return match (class_basename($scope)) {
            'Shop' => array_merge(
                ShowShop::make()->getBreadcrumbs(
                    $routeParameters
                ),
                [
                    [
                        'type'          => 'creatingModel',
                        'creatingModel' => [
                            'label' => __("creating website"),
                        ]
                    ]
                ]
            ),
            'Organisation' => array_merge(
                IndexWebsites::make()->getBreadcrumbs(
                    'grp.org.shops.show.websites.index',
                    []
                ),
                [
                    [
                        'type'          => 'creatingModel',
                        'creatingModel' => [
                            'label' => __("creating website"),
                        ]
                    ]
                ]
            ),
        };
    }


}
