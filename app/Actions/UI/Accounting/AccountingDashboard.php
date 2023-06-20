<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:42:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Accounting;

use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\UI\Dashboard\Dashboard;
use App\Actions\UI\WithInertia;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class AccountingDashboard
{
    use AsAction;
    use WithInertia;


    public function handle($scope)
    {
        return $scope;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("accounting.view");
    }


    public function inTenant(): Tenant
    {
        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop): Shop
    {
        return $this->handle($shop);
    }


    public function htmlResponse(Tenant|Shop $scope, ActionRequest $request): Response
    {
        $container = null;
        $scopeType = 'Tenant';
        if (class_basename($scope) == 'Shop') {
            $scopeType = 'Shop';
            $container = [
                'icon'    => ['fal', 'fa-store-alt'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($scope->name)
            ];
        }


        return Inertia::render(
            'Accounting/AccountingDashboard',
            [
                'breadcrumbs'  => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->parameters
                ),
                'title'        => __('accounting'),
                'pageHead'     => [
                    'title'     => __('accounting'),
                    'container' => $container
                ],
                'flatTreeMaps' =>
                    match ($scopeType) {
                        'Shop' => [
                            [

                                [
                                    'name'  => __('accounts'),
                                    'icon'  => ['fal', 'fa-money-check-alt'],
                                    'href'  => ['shops.show.accounting.payment-accounts.index', $scope->slug],
                                    'index' => [
                                        'number' => $scope->accountingStats->number_payment_accounts
                                    ]

                                ],
                                [
                                    'name'  => __('payments'),
                                    'icon'  => ['fal', 'fa-coins'],
                                    'href'  => ['shops.show.accounting.payments.index', $scope->slug],
                                    'index' => [
                                        'number' => $scope->accountingStats->number_payments
                                    ]

                                ],
                                [
                                    'name'  => __('invoices'),
                                    'icon'  => ['fal', 'fa-file-invoice-dollar'],
                                    'href'  => ['shops.show.accounting.invoices.index', $scope->slug],
                                    'index' => [
                                        'number' => $scope->accountingStats->number_invoices
                                    ]

                                ],

                            ]
                        ],
                        default => [
                            [


                                [
                                    'name'  => __('providers'),
                                    'icon'  => ['fal', 'fa-cash-register'],
                                    'href'  => ['accounting.payment-service-providers.index'],
                                    'index' => [
                                        'number' => $scope->accountingStats->number_payment_service_providers
                                    ]

                                ],
                                [
                                    'name'  => __('accounts'),
                                    'icon'  => ['fal', 'fa-money-check-alt'],
                                    'href'  => ['accounting.payment-accounts.index'],
                                    'index' => [
                                        'number' => $scope->accountingStats->number_payment_accounts
                                    ]

                                ],
                                [
                                    'name'  => __('payments'),
                                    'icon'  => ['fal', 'fa-coins'],
                                    'href'  => ['accounting.payments.index'],
                                    'index' => [
                                        'number' => $scope->accountingStats->number_payments
                                    ]

                                ],

                            ],
                            [
                                [
                                    'name'  => __('invoices'),
                                    'icon'  => ['fal', 'fa-file-invoice-dollar'],
                                    'href'  => ['accounting.invoices.index'],
                                    'index' => [
                                        'number' => $scope->accountingStats->number_invoices
                                    ]

                                ],

                            ]
                        ]
                    }


            ]
        );
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return match ($routeName) {
            'shops.show.accounting.dashboard' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'shops.show.accounting.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('accounting'),
                        ]
                    ]
                ]
            ),
            default =>
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
            )
        };
    }
}
