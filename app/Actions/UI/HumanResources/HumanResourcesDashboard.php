<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:46:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\HumanResources;

use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\UI\WithInertia;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class HumanResourcesDashboard
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("hr.view");
    }


    public function asController(): void
    {
        $this->validateAttributes();
    }


    public function htmlResponse(): Response
    {
        /** @var \App\Models\Central\Tenant $tenant */
        $tenant = app('currentTenant');

        return Inertia::render(
            'HumanResources/HumanResourcesDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('human resources'),
                'pageHead'    => [
                    'title' => __('human resources'),
                ],
                'stats'       => [
                    [
                        'name' => __('employees'),
                        'stat' => $tenant->stats->number_employees,
                        'href' => ['hr.employees.index']
                    ]
                ]

            ]
        );
    }


    public function getBreadcrumbs(): array
    {
        return
            array_merge(
                Dashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'hr.dashboard'
                            ],
                            'label' => __('human resources'),
                        ]
                    ]
                ]
            );
    }
}
