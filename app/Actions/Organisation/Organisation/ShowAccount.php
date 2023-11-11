<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 23 Apr 2023 11:33:30 Malaysia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Organisation\Organisation;

use App\Actions\InertiaAction;
use App\Models\Organisation\Organisation;
use Inertia\Inertia;
use Inertia\Response;

class ShowAccount extends InertiaAction
{
    private Organisation $organisation;

    public function asController(): void
    {
        $this->tenant = app('currentTenant');
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
