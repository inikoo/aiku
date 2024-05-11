<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 04 Oct 2023 18:36:33 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Prospect\UI;

use App\Actions\InertiaAction;
use App\Models\Catalogue\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateProspect extends InertiaAction
{
    public function handle(Shop $parent, ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('new prospect'),
                'pageHead'    => [
                    'title'   => __('new prospect'),
                    'icon'    => [
                        'icon'  => ['fal', 'fa-transporter'],
                        'title' => __('prospect')
                    ],
                    'actions' => [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'label' => __('cancel'),
                            'route' => [
                                'name'       => preg_replace('/create$/', 'index', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ],
                        ]
                    ]
                ],
                'formData'    => [
                    'blueprint' =>
                        [
                            [
                                'title'  => __('contact'),
                                'fields' => [
                                    'company_name' => [
                                        'type'  => 'input',
                                        'label' => __('company')
                                    ],
                                    'contact_name' => [
                                        'type'  => 'input',
                                        'label' => __('contact name')
                                    ],
                                    'email'        => [
                                        'type'  => 'input',
                                        'label' => __('email')
                                    ],
                                    'phone'        => [
                                        'type'  => 'phone',
                                        'label' => __('phone')
                                    ],
                                    'contact_website'  => [
                                        'type'      => 'inputWithAddOn',
                                        'label'     => __('website'),
                                        'leftAddOn' => [
                                            'label' => 'https://'
                                        ],
                                    ],

                                ]
                            ],
                        ],
                    'route'     =>
                        match (class_basename($parent)) {
                            'Shop' => [
                                'name'       => 'org.models.shop.prospect.store',
                                'parameters' => [$parent->id]
                            ],
                            default => [
                                [
                                    'name' => 'org.models.prospect.store',
                                ]
                            ]
                        }
                ]

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('crm.edit');
    }


    public function inShop(Shop $shop, ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($shop, $request);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return array_merge(
            IndexProspects::make()->getBreadcrumbs(
                routeName: preg_replace('/create$/', 'index', $routeName),
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('creating prospect'),
                    ]
                ]
            ]
        );
    }
}
