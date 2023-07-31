<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Fulfilment;

use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\Tenancy\Tenant;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class FulfilmentDashboard
{
    use AsAction;
    use WithInertia;


    private ?Tenant $tenant;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilment.view");
    }


    public function asController(): void
    {
        $this->tenant = app('currentTenant');
    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Fulfilment/FulfilmentDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('fulfilment'),
                'pageHead'    => [
                    'title' => __('fulfilment'),
                ],
                'flatTreeMaps'    => [

                    [
                        [
                            'name'  => __('Customers'),
                            'icon'  => ['fal', 'fa-user-tie'],
                            'href'  => ['fulfilment.customers.index'],
                            'index' => [
                                'number' => $this->tenant->fulfilmentStats->number_customers_with_stored_items
                            ],

                        ],
                        [
                            'name'  => __('Stored Items'),
                            'icon'  => ['fal', 'fa-narwhal'],
                            'href'  => ['fulfilment.stored-items.index'],
                            'index' => [
                                'number' => $this->tenant->fulfilmentStats->number_stored_items
                            ],

                        ],

                        [
                            'name'  => __('Orders'),
                            'icon'  => ['fal', 'fa-business-time'],
                            'href'  => ['fulfilment.orders.index'],
                            'index' => [
                                'number' => $this->tenant->fulfilmentStats->number_customers_with_assets
                            ],

                        ]
                    ]
                ]

            ]
        );
    }

    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'fulfilment.dashboard'
                            ],
                            'label' => __('fulfilment'),
                        ]
                    ]
                ]
            );
    }



}
