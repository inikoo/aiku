<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Tue, 30 Jan 2024 09:51:20 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\UI;

use App\Actions\OrgAction;
use App\Actions\Traits\Fields\StoreCustomerFields;
use App\Models\Fulfilment\Fulfilment;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreatePalletDelivery extends OrgAction
{
    use StoreCustomerFields;

    public function handle(Fulfilment $fulfilment, ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'title'       => __('new pallet delivery'),
                'pageHead'    => [
                    'title'        => __('new pallet delivery'),
                    'icon'         => [
                        'icon'  => ['fal', 'fa-user'],
                        'title' => __('pallet delivery')
                    ],
                    'actions'      => [
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
                    'blueprint' => [
                        [
                            'title'  => __('contact'),
                            'fields' => [
                                'name' => [
                                    'type'  => 'input',
                                    'label' => __('name')
                                ]
                            ]
                        ]
                    ],

                    'route'     => [
                        'name'     => 'grp.models.shop.customer.store',
                        'arguments'=> [$fulfilment->id]
                    ]
                ]

            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("crm.{$this->shop->id}.edit");
    }


    public function asController(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): Response
    {
        $this->initialisationFromFulfilment($fulfilment, $request);
        return $this->handle($fulfilment, $request);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        return array_merge(
            IndexFulfilmentCustomers::make()->getBreadcrumbs(
                routeParameters: $routeParameters,
            ),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __('Creating customer'),
                    ]
                ]
            ]
        );
    }
}
