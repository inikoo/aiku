<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 06 Mar 2023 18:42:58 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\UI\Accounting;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\OrgAction;
use App\Actions\UI\Grp\Dashboard\ShowDashboard;
use App\Actions\UI\WithInertia;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowAccountingDashboard extends OrgAction
{
    use AsAction;
    use WithInertia;


    private Organisation|Shop $scope;


    public function authorize(ActionRequest $request): bool
    {
        if ($this->scope instanceof Organisation) {
            return $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.view");
        }

        return false;
    }


    public function inOrganisation(Organisation $organisation, ActionRequest $request): ActionRequest
    {
        $this->scope = $organisation;
        $this->initialisation($organisation, $request);

        return $request;
    }

    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): ActionRequest
    {
        $this->scope = $shop;
        $this->initialisationFromShop($shop, $request);

        return $request;
    }


    public function htmlResponse(ActionRequest $request): Response
    {
        $container = null;
        $scopeType = 'Organisation';
        if (class_basename($this->scope) == 'Shop') {
            $scopeType = 'Shop';
            $container = [
                'icon'    => ['fal', 'fa-store-alt'],
                'tooltip' => __('Shop'),
                'label'   => Str::possessive($this->scope->name)
            ];
        }

        $parameters = $request->route()->originalParameters();

        return Inertia::render(
            'Org/Accounting/AccountingDashboard',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('accounting'),
                'pageHead'    => [
                    'icon'      => [
                        'icon'  => ['fal', 'fa-abacus'],
                        'title' => __('accounting')
                    ],
                    'iconRight' => [
                        'icon'  => ['fal', 'fa-chart-network'],
                        'title' => __('accounting')
                    ],
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
                                    'href'  => [
                                        'name'       => 'grp.org.accounting.shops.show.payment-accounts.index',
                                        'parameters' => $parameters
                                    ],
                                    'index' => [
                                        'number' => $this->scope->accountingStats->number_payment_accounts
                                    ]

                                ],
                                [
                                    'name'  => __('payments'),
                                    'icon'  => ['fal', 'fa-coins'],
                                    'href'  => [
                                        'name'       => 'grp.org.accounting.shops.show.payments.index',
                                        'parameters' => $parameters
                                    ],
                                    'index' => [
                                        'number' => $this->scope->accountingStats->number_payments
                                    ]

                                ],
                                [
                                    'name'  => __('invoices'),
                                    'icon'  => ['fal', 'fa-file-invoice-dollar'],
                                    'href'  => [
                                        'name'       => 'grp.org.accounting.shops.show.invoices.index',
                                        'parameters' => $parameters
                                    ],
                                    'index' => [
                                        'number' => $this->scope->accountingStats->number_invoices
                                    ]

                                ],

                            ]
                        ],
                        default => [
                            [

                                [
                                    'name'         => __('accounts'),
                                    'icon'         => ['fal', 'fa-money-check-alt'],
                                    'href'         => [
                                        'name'       => 'grp.org.accounting.payment-accounts.index',
                                        'parameters' => $parameters
                                    ],
                                    'index'        => [
                                        'number' => $this->scope->accountingStats->number_payment_accounts
                                    ],
                                    'rightSubLink' => [
                                        'tooltip'    => __('payment methods'),
                                        'icon'       => ['fal', 'fa-cash-register'],
                                        'labelStyle' => 'bordered',
                                        'href'       => [
                                            'name'       => 'grp.org.accounting.org-payment-service-providers.index',
                                            'parameters' => $parameters
                                        ],

                                    ]

                                ],
                                [
                                    'name'  => __('payments'),
                                    'icon'  => ['fal', 'fa-coins'],
                                    'href'  => [
                                        'name'       => 'grp.org.accounting.payments.index',
                                        'parameters' => $parameters
                                    ],
                                    'index' => [
                                        'number' => $this->scope->accountingStats->number_payments
                                    ]


                                ],
                                [
                                    'name'  => __('invoices'),
                                    'icon'  => ['fal', 'fa-file-invoice-dollar'],
                                    'href'  => [
                                        'name'       => 'grp.org.accounting.invoices.index',
                                        'parameters' => $parameters
                                    ],
                                    'index' => [
                                        'number' => $this->scope->accountingStats->number_invoices
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
            'grp.org.accounting.shops.show.dashboard' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                [
                    [
                        'type'   => 'simple',
                        'simple' => [
                            'route' => [
                                'name'       => 'grp.org.accounting.shops.show.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Accounting'),
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
                                'name'       => 'grp.org.accounting.dashboard',
                                'parameters' => $routeParameters
                            ],
                            'label' => __('Accounting'),
                        ]
                    ]
                ]
            )
        };
    }
}
