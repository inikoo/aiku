<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 02 Mar 2023 21:32:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Central\Tenant;

use App\Actions\InertiaAction;
use App\Models\Central\Tenant;
use Inertia\Inertia;
use Inertia\Response;


class ShowAccount extends InertiaAction
{


    private Tenant $tenant;

    public function asController(): void
    {
        $this->tenant = tenant();
    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Central/Account',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('account'),
                'pageHead'    => [
                    'title' => $this->tenant->name,
                ],

                'tabs' => [

                    'current' => 'account',
                    'items'   => [
                        'account' => [
                            'name' => __('Account'),
                            'icon' => 'fal fa-briefcase',
                        ],
                        'index'   => [
                            'name' => __('Index'),
                            'icon' => 'fal fa-indent',
                        ]
                    ]


                ],

                'contents' => [
                    [
                        'title' => __('Inventory'),
                        'items' => [
                            [
                                'title' => __('Warehouses'),
                                'count' => $this->tenant->inventoryStats->number_warehouses,
                                'href'  => ['index.warehouses.index']
                            ]
                        ]
                    ]
                ]


            ]

        );
    }


    public function getBreadcrumbs(): array
    {
        return [];
    }


}
