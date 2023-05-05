<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:47:05 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Procurement;

use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\UI\WithInertia;
use App\Models\Auth\User;
use App\Models\Tenancy\Tenant;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @property Tenant $tenant
 * @property User $user
 */
class ProcurementDashboard
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("procurement.view");
    }


    public function asController(ActionRequest $request): void
    {
        $this->user   = $request->user();
        $this->tenant = app('currentTenant');
    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();

        return Inertia::render(
            'Procurement/ProcurementDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('procurement'),
                'pageHead'    => [
                    'title' => __('procurement'),
                ],
                'flatTreeMaps'    => [

                    [
                        [
                            'name'  => __('agents'),
                            'icon'  => ['fal', 'fa-people-arrows'],
                            'href'  => ['procurement.agents.index'],
                            'index' => [
                                'number' => $this->tenant->procurementStats->number_active_agents
                            ]

                        ],
                        [
                            'name'  => __('suppliers'),
                            'icon'  => ['fal', 'fa-person-dolly'],
                            'href'  => ['procurement.suppliers.index'],
                            'index' => [
                                'number' => $this->tenant->procurementStats->number_active_suppliers
                            ]

                        ],
                        [
                            'name'  => __('Supplier Product'),
                            'icon'  => ['fal', 'fa-parachute-box'],
                            'href'  => ['procurement.supplier-products.index'],
                            'index' => [
                                'number' => $this->tenant->procurementStats->number_products
                            ]

                        ],
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
                                'name' => 'procurement.dashboard'
                            ],
                            'label' => __('procurement'),
                        ]
                    ]
                ]
            );
    }


}
