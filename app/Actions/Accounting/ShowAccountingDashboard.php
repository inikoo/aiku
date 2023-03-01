<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 01 Mar 2023 14:52:41 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting;

use App\Actions\UI\WithInertia;
use App\Models\Central\Tenant;
use App\Models\Inventory\Warehouse;
use App\Models\SysAdmin\User;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;


/**
 * @property Tenant $tenant
 * @property User $user
 */
class ShowAccountingDashboard
{
    use AsAction;
    use WithInertia;

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("accounting.view");
    }


    public function asController(ActionRequest $request): void
    {
        $this->user   = $request->user();
        $this->tenant = tenant();
    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();


     /*
            $warehousesNode     = [
                'name' => __('providers'),
                'icon' => ['fal', 'fa-warehouse'],
                'href' => ['inventory.warehouses.show', $warehouse->slug],

            ];
            $warehouseAreasNode = [
                'name'  => __('accounts'),
                'icon'  => ['fal', 'fa-map-signs'],
                'href'  => ['inventory.warehouses.show.warehouse_areas.index', $warehouse->slug],
                'index' => [
                    'number' => $this->tenant->inventoryStats->number_warehouse_areas
                ]
            ];
            $locationsNode      = [
                'name'  => __('payments'),
                'icon'  => ['fal', 'fa-inventory'],
                'href'  => ['inventory.warehouses.show.locations.index', $warehouse->slug],
                'index' => [
                    'number' => $this->tenant->inventoryStats->number_locations
                ]

            ];
   */


        return Inertia::render(
            'Accounting/AccountingDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('accounting'),
                'pageHead'    => [
                    'title' => __('accounting'),
                ],
                'treeMaps'    => [
                    [
                        [
                            'name'  => __('providers'),
                            'icon'  => ['fal', 'fa-boxes-alt'],
                            'href'  => ['accounting.payment-service-providers.index'],
                            'index' => [
                                'number' => $this->tenant->accountingStats->number_payment_service_providers
                            ]

                        ],
                        [
                            'name'  => __('accounts'),
                            'icon'  => ['fal', 'fa-box'],
                            'href'  => ['accounting.payment-accounts.index'],
                            'index' => [
                                'number' => $this->tenant->accountingStats->number_payment_accounts
                            ]

                        ],
                        [
                            'name'  => __('payments'),
                            'icon'  => ['fal', 'fa-box'],
                            'href'  => ['accounting.payments.index'],
                            'index' => [
                                'number' => $this->tenant->accountingStats->number_payments
                            ]

                        ],

                    ],
                    [
                        [
                            'name'  => __('invoices'),
                            'icon'  => ['fal', 'fa-boxes-alt'],
                            'href'  => ['inventory.stock-families.index'],
                            'index' => [
                                'number' => $this->tenant->inventoryStats->number_stock_families
                            ]

                        ],

                    ]
                ]

            ]

        );
    }


    public function getBreadcrumbs(): array
    {
        return [
            'accounting.dashboard' => [
                'route' => 'accounting.dashboard',
                'name'  => __('accounting'),
            ]
        ];
    }


}
