<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:42:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Accounting;

use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\UI\WithInertia;
use App\Models\SysAdmin\User;
use App\Models\Tenancy\Tenant;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @property Tenant $tenant
 * @property User $user
 */
class AccountingDashboard
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
        $this->tenant = app('currentTenant');
    }


    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Accounting/AccountingDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('accounting'),
                'pageHead'    => [
                    'title' => __('accounting'),
                ],
                'flatTreeMaps'    => [
                    [
                        [
                            'name'  => __('providers'),
                            'icon'  => ['fal', 'fa-cash-register'],
                            'href'  => ['accounting.payment-service-providers.index'],
                            'index' => [
                                'number' => $this->tenant->accountingStats->number_payment_service_providers
                            ]

                        ],
                        [
                            'name'  => __('accounts'),
                            'icon'  => ['fal', 'fa-money-check-alt'],
                            'href'  => ['accounting.payment-accounts.index'],
                            'index' => [
                                'number' => $this->tenant->accountingStats->number_payment_accounts
                            ]

                        ],
                        [
                            'name'  => __('payments'),
                            'icon'  => ['fal', 'fa-coins'],
                            'href'  => ['accounting.payments.index'],
                            'index' => [
                                'number' => $this->tenant->accountingStats->number_payments
                            ]

                        ],

                    ],
                    [
                        // TODO Raul please fix the stats
                        [
                            'name'  => __('invoices'),
                            'icon'  => ['fal', 'fa-file-invoice-dollar'],
                            'href'  => ['accounting.invoices.index'],
                            'index' => [
                                'number' => $this->tenant->inventoryStats->number_stocks
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
                                'name' => 'accounting.dashboard'
                            ],
                            'label' => __('accounting'),
                        ]
                    ]
                ]
            );
    }
}
