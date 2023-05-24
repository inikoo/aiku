<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 18 May 2023 14:27:30 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Marketing\Shop\UI;

use App\Actions\InertiaAction;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreateShop extends InertiaAction
{
    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('new shop'),
                'pageHead'    => [
                    'title'        => __('new shop'),
                    'cancelCreate' => [
                        'route' => [
                            'name'       => 'shops.index',
                            'parameters' => array_values($this->originalParameters)
                        ],
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
                                    'value' => ''
                                ],
                                'name' => [
                                    'type'   => 'input',
                                    'label'  => __('name'),
                                    'value'  => '',
                                    'options'=> [
                                        'counter'=> true
                                    ]
                                ],
                            ]
                        ],
                        /*
                        [
                            'title'  => __('localization'),
                            'icon'   => 'fa-light fa-phone',
                            'fields' => [
                                'language_id' => [
                                    'type'    => 'input',
                                    'label'   => __('language'),
                                    'value'   => '',
                                ],
                                'currency_id' => [
                                    'type'    => 'input',
                                    'label'   => __('currency'),
                                    'value'   => '',
                                ],
                                'timezone_id' => [
                                    'type'    => 'input',
                                    'label'   => __('timezone'),
                                    'value'   => '',
                                ],
                                'country_id' => [
                                    'type'    => 'input',
                                    'label'   => __('country'),
                                    'value'   => '',
                                ],

                            ]
                        ],
                        [
                            'title'  => __('contact/details'),
                            'fields' => [
                                'contact_name' => [
                                    'type'    => 'input',
                                    'label'   => __('contact name'),
                                    'value'   => '',
                                ],
                                'company_name' => [
                                    'type'    => 'input',
                                    'label'   => __('company name'),
                                    'value'   => '',
                                ],
                                'email' => [
                                    'type'    => 'input',
                                    'label'   => __('email'),
                                    'value'   => '',
                                    'options' => [
                                        'inputType' => 'email'
                                    ]
                                ],
                                'phone' => [
                                    'type'  => 'input',
                                    'label' => __('telephone'),
                                    'value' => ''
                                ],
                            ]
                        ],
                        */
                    ],
                    'route' => [
                        'name' => 'models.shop.store',
                    ]
                ],

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('shops.edit');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            IndexShops::make()->getBreadcrumbs(),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __("creating shop"),
                    ]
                ]
            ]
        );
    }
}
