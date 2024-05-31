<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 18 Feb 2024 07:11:09 Central Standard Time, Mexico City, Mexico
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Grp\Dashboard;

use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use Illuminate\Http\Resources\Json\JsonResource;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDashboard
{
    use AsAction;

    public function handle(): Response
    {
        /** @var Group $group */
        $group = Group::first();
        $sales = [
            'sales'         => JsonResource::make($group->salesStats),
            'currency'      => $group->currency,
            'organisations' => $group->organisations->map(function (Organisation $organisation) {
                return [
                    'name'      => $organisation->name,
                    'code'      => $organisation->code,
                    'type'      => $organisation->type,
                    'currency'  => $organisation->currency,
                    'sales'     => $organisation->salesIntervals,
                    'invoices'  => [
                        'number_invoices' => $organisation->accountingStats->number_invoices_type_invoice
                    ],
                    'refunds' => [
                        'number_refunds' => $organisation->accountingStats->number_invoices_type_refund
                    ]
                ];
            })
        ];

        return Inertia::render(
            'Dashboard/Dashboard',
            [
                'breadcrumbs'      => $this->getBreadcrumbs(__('Dashboard')),
                'groupStats'       => $sales,
            ]
        );
    }

    public function getBreadcrumbs($label=null): array
    {
        return [
            [

                'type'   => 'simple',
                'simple' => [
                    'icon'  => 'fal fa-tachometer-alt-fast',
                    'label' => $label,
                    'route' => [
                        'name' => 'grp.dashboard.show'
                    ]
                ]

            ],

        ];
    }
}
