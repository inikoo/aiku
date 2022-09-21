<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 07 Feb 2022 14:24:56 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Actions\HumanResources;

use App\Actions\UI\WithInertia;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


class ShowHumanResourcesDashboard
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.view");
    }


    public function asController(): bool
    {
        return true;
    }


    public function htmlResponse(): Response
    {

        $tenant=tenant();
        $tenantStats=$tenant->tenantStats;
        return Inertia::render(
            'HumanResources/HumanResourcesDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('human resources'),
                'pageHead'    => [
                    'title' => __('human resources'),
                ],
                'stats' => [
                    [
                        'name' => __('employees'),
                        'stat' => $tenantStats->number_employees,
                        'href' => ['hr.employees.index']
                    ]
                ]

            ]

        );
    }


    public function getBreadcrumbs(): array
    {
        return [
            'hr.dashboard' => [
                'route' => 'hr.dashboard',
                'name'  => __('human resources'),
            ]
        ];
    }


}
