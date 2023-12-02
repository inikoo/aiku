<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:42:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Accounting;

use App\Actions\Market\Shop\UI\ShowShop;
use App\Actions\UI\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\Market\Shop;
use App\Models\Grouping\Organisation;
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


    public function inTenant(): Organisation
    {
        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop): Shop
    {
        return $this->handle($shop);
    }


    public function htmlResponse(Organisation|Shop $scope, ActionRequest $request): Response
    {
        $container = null;
        $scopeType = 'Organisation';
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
                                    'href'  => ['grp.accounting.shops.show.payment-accounts.index', $scope->slug],
                                    'index' => [
                                        'number' => $scope->accountingStats->number_payment_accounts
                                    ]

                                ],
                                [
                                    'name'  => __('payments'),
                                    'icon'  => ['fal', 'fa-coins'],
                                    'href'  => ['grp.accounting.shops.show.payments.index', $scope->slug],
                                    'index' => [
                                        'number' => $scope->accountingStats->number_payments
                                    ]

                                ],
                                [
                                    'name'  => __('invoices'),
                                    'icon'  => ['fal', 'fa-file-invoice-dollar'],
                                    'href'  => ['grp.accounting.shops.show.invoices.index', $scope->slug],
                                    'index' => [
                                        'number' => $scope->accountingStats->number_invoices
                                    ]

                                ],

                            ]
                        ],
                        default => [
                            [

                                [
                                    'name'  => __('accounts'),
                                    'icon'  => ['fal', 'fa-money-check-alt'],
                                    'href'  => ['grp.accounting.payment-accounts.index'],
                                    'index' => [
                                        'number' => $scope->accountingStats->number_payment_accounts
                                    ],
                                    'rightSubLink' => [
                                        'tooltip'    => __('payment methods'),
                                        'icon'       => ['fal', 'fa-cash-register'],
                                        'labelStyle' => 'bordered',
                                        'href'       => ['grp.accounting.payment-service-providers.index'],

                                    ]

                                ],
                                [
                                    'name'  => __('payments'),
                                    'icon'  => ['fal', 'fa-coins'],
                                    'href'  => ['grp.accounting.payments.index'],
                                    'index' => [
                                        'number' => $scope->accountingStats->number_payments
                                    ]

                                ],
                                [
                                    'name'  => __('invoices'),
                                    'icon'  => ['fal', 'fa-file-invoice-dollar'],
                                    'href'  => ['grp.accounting.invoices.index'],
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
            'grp.accounting.shops.show.dashboard' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.accounting.shops.show.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('accounting'),
                        ]
                    ]
                ]
            ),
            default =>
            array_merge(
                ShowDashboard::make()->getBreadcrumbs(),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name' => 'grp.accounting.dashboard'
                            ],
                            'label' => __('accounting'),
                        ]
                    ]
                ]
            )
        };
    }
}
