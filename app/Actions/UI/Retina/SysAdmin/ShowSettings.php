<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 19 Feb 2024 23:54:37 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Retina\SysAdmin;

use App\Actions\Helpers\Country\UI\GetAddressData;
use App\Actions\RetinaAction;
use App\Actions\UI\Retina\Dashboard\ShowRetinaDashboard;
use App\Http\Resources\Helpers\AddressFormFieldsResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowSettings extends RetinaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->is_root;
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);

        return $this->handle($request);
    }


    public function handle(ActionRequest $request): Response
    {

        $customer = $request->user()->customer;
        $fulfilmentCustomer = $customer->fulfilmentCustomer;

        return Inertia::render(
            'EditModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('settings'),
                'pageHead'    => [
                    'title' => __('settings'),
                ],
                "formData" => [
                    "blueprint" =>
                    [
                        [
                            'title'  => __('contact information'),
                            'label'  => __('contact'),
                            'icon'    => 'fa-light fa-address-book',
                            'fields' => [
                                    'contact_name' => [
                                        'type'  => 'input',
                                        'label' => __('contact name'),
                                        'value' => $customer->contact_name
                                    ],
                                    'company_name' => [
                                        'type'  => 'input',
                                        'label' => __('company'),
                                        'value' => $customer->company_name
                                    ],
                                    'phone'        => [
                                        'type'  => 'phone',
                                        'label' => __('Phone'),
                                        'value' => $customer->phone
                                    ],
                                    'address' => [
                                        'type'    => 'address',
                                        'label'   => __('Address'),
                                        'value'   => AddressFormFieldsResource::make($customer->address)->getArray(),
                                        'options' => [
                                            'countriesAddressData' => GetAddressData::run()
                                        ]
                                    ]
                                ]
                        ]
                    ],
                    "args"      => [
                        "updateRoute" => [
                            "name"       => "retina.models.fulfilment-customer.update",
                            'parameters' => [$fulfilmentCustomer->id]
                        ],
                    ],
                ],


            ]
        );
    }



    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ShowRetinaDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'retina.sysadmin.settings.edit'
                            ],
                            'label'  => __('settings'),
                        ]
                    ]
                ]
            );
    }
}
