<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:44:51 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Fulfilment;

use App\Actions\OrgAction;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class FulfilmentDashboard extends OrgAction
{
    use AsAction;
    use WithInertia;


    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("fulfilments.{}.view");
    }


    public function asController(): void
    {
        $this->tenant = app('currentTenant');
    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Org/Fulfilment/FulfilmentDashboard',
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
                            'href'  => ['grp.fulfilment.customers.index'],
                            'index' => [
                                'number' => $this->tenant->fulfilmentStats->number_customers_with_stored_items
                            ],

                        ],
                        [
                            'name'  => __('Stored Items'),
                            'icon'  => ['fal', 'fa-narwhal'],
                            'href'  => ['grp.fulfilment.stored-items.index'],
                            'index' => [
                                'number' => $this->tenant->fulfilmentStats->number_stored_items
                            ],

                        ],

                        [
                            'name'  => __('Orders'),
                            'icon'  => ['fal', 'fa-business-time'],
                            'href'  => ['grp.fulfilment.orders.index'],
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
                                'name' => 'grp.fulfilment.dashboard'
                            ],
                            'label' => __('fulfilment'),
                        ]
                    ]
                ]
            );
    }



}
