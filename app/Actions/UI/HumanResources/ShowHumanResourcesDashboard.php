<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:46:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\HumanResources;

use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\Organisation\Organisation;
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


    public function asController(): void
    {
        $this->validateAttributes();
    }


    public function htmlResponse(): Response
    {
        /** @var Organisation $organisation */
        $organisation = app('currentTenant');

        return Inertia::render(
            'HumanResources/ShowHumanResourcesDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('human resources'),
                'pageHead'    => [
                    'title' => __('human resources'),
                ],
                'stats'       => [
                    [
                        'name' => __('employees'),
                        'stat' => $organisation->stats->number_employees,
                        'href' => ['grp.hr.employees.index']
                    ],
                    [
                        'name' => __('working places'),
                        'stat' => $organisation->stats->number_employees_state_working,
                        'href' => ['grp.hr.working-places.index']
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
                                'name' => 'grp.hr.dashboard'
                            ],
                            'label' => __('human resources'),
                        ]
                    ]
                ]
            );
    }
}
