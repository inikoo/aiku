<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 15:40:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\PaymentAccount\UI;

use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Accounting\PaymentServiceProvider\UI\ShowPaymentServiceProvider;
use App\Actions\Helpers\History\IndexHistory;
use App\Actions\OrgAction;
use App\Actions\UI\Accounting\ShowAccountingDashboard;
use App\Enums\UI\Accounting\PaymentAccountTabsEnum;
use App\Http\Resources\Accounting\PaymentAccountsResource;
use App\Http\Resources\Accounting\PaymentsResource;
use App\Http\Resources\History\HistoryResource;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property PaymentAccount $paymentAccount
 */
class ShowPaymentAccount extends OrgAction
{
    public function handle(PaymentAccount $paymentAccount): PaymentAccount
    {
        return $paymentAccount;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit   = $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.edit");
        $this->canDelete = $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.edit");

        return $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.view");
    }

    public function inOrganisation(Organisation $organisation, PaymentAccount $paymentAccount, ActionRequest $request): PaymentAccount
    {
        $this->initialisation($organisation, $request)->withTab(PaymentAccountTabsEnum::values());

        return $this->handle($paymentAccount);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentServiceProvider(Organisation $organisation, PaymentServiceProvider $paymentServiceProvider, PaymentAccount $paymentAccount, ActionRequest $request): PaymentAccount
    {
        $this->initialisation($organisation, $request)->withTab(PaymentAccountTabsEnum::values());

        return $this->handle($paymentAccount);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, PaymentAccount $paymentAccount, ActionRequest $request): PaymentAccount
    {
        $this->initialisationFromShop($shop, $request)->withTab(PaymentAccountTabsEnum::values());

        return $this->handle($paymentAccount);
    }

    public function htmlResponse(PaymentAccount $paymentAccount, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Accounting/PaymentAccount',
            [
                'title'       => $paymentAccount->name,
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'navigation'  => [
                    'previous' => $this->getPrevious($paymentAccount, $request),
                    'next'     => $this->getNext($paymentAccount, $request),
                ],
                'pageHead'    => [
                    'icon'   =>
                        [
                            'icon'  => ['fal', 'fa-money-check-alt'],
                            'title' => __('payment account')
                        ],
                    'title'  => $paymentAccount->slug,
                    'create' => $this->canEdit
                    && (
                        $request->route()->getName() == 'grp.org.accounting.org-payment-service-providers.show.payment-accounts.show' or
                        $request->route()->getName() == 'grp.org.accounting.payment-accounts.show'
                    ) ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'show.payments.create', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ],
                        'label' => __('payment')
                    ] : false,
                    'meta'   => [
                        [
                            'name'     => trans_choice('payment | payments', $paymentAccount->stats->number_payments),
                            'number'   => $paymentAccount->stats->number_payments,
                            'href'     => match ($request->route()->getName()) {
                                'grp.org.accounting.org-payment-service-providers.show.payment-accounts.show' => [
                                    'name'       => 'grp.org.accounting.org-payment-service-providers.show.payment-accounts.show.payments.index',
                                    'parameters' => [$paymentAccount->paymentServiceProvider->slug, $paymentAccount->slug]
                                ],
                                default => [
                                    'name'       => 'grp.org.accounting.payment-accounts.show.payments.index',
                                    'parameters' => [
                                        $this->organisation,
                                        $paymentAccount->slug
                                    ]
                                ]
                            },
                            'leftIcon' => [
                                'icon'    => 'fal fa-coins',
                                'tooltip' => __('payments')
                            ]
                        ],

                    ],

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => PaymentAccountTabsEnum::navigation()

                ],

                PaymentAccountTabsEnum::PAYMENTS->value => $this->tab == PaymentAccountTabsEnum::PAYMENTS->value
                    ?
                    fn () => PaymentsResource::collection(
                        IndexPayments::run(
                            parent: $this->paymentAccount,
                            prefix: 'payments'
                        )
                    )
                    : Inertia::lazy(fn () => PaymentsResource::collection(
                        IndexPayments::run(
                            parent: $this->paymentAccount,
                            prefix: 'payments'
                        )
                    )),
                PaymentAccountTabsEnum::HISTORY->value  => $this->tab == PaymentAccountTabsEnum::HISTORY->value ?
                    fn () => HistoryResource::collection(IndexHistory::run($paymentAccount))
                    : Inertia::lazy(fn () => HistoryResource::collection(IndexHistory::run($paymentAccount)))

            ]
        )->table(
            IndexPayments::make()->tableStructure(
                parent: $paymentAccount,
                modelOperations: [
                    'createLink' => $this->canEdit ? [
                        'route' => [
                            'name'       => 'grp.org.accounting.payment-accounts.show.payments.create',
                            'parameters' => array_values([$paymentAccount->slug])
                        ],
                        'label' => __('products')
                    ] : false
                ],
                prefix: 'payments'
            )
        )->table(IndexHistory::make()->tableStructure());
    }


    public function jsonResponse(PaymentAccount $paymentAccount): PaymentAccountsResource
    {
        return new PaymentAccountsResource($paymentAccount);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (PaymentAccount $paymentAccount, array $routeParameters, string $suffix) {
            return [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('payment accounts')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $paymentAccount->code,
                        ],

                    ],
                    'suffix'         => $suffix
                ],
            ];
        };

        $paymentAccount = PaymentAccount::where('slug', $routeParameters['paymentAccount'])->firstOrFail();

        return match ($routeName) {
            'grp.org.accounting.shops.show.payment-accounts.show' =>
            array_merge(
                (new  ShowAccountingDashboard())->getBreadcrumbs('grp.org.accounting.shops.show.dashboard', $routeParameters),
                $headCrumb(
                    $paymentAccount,
                    [
                        'index' => [
                            'name'       => 'grp.org.accounting.shops.show.payment-accounts.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.accounting.shops.show.payment-accounts.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.accounting.payment-accounts.show' =>
            array_merge(
                (new  ShowAccountingDashboard())->getBreadcrumbs('grp.org.accounting.dashboard', $routeParameters),
                $headCrumb(
                    $paymentAccount,
                    [
                        'index' => [
                            'name'       => 'grp.org.accounting.payment-accounts.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.accounting.payment-accounts.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            'grp.org.accounting.org-payment-service-providers.show.payment-accounts.show' =>
            array_merge(
                ShowPaymentServiceProvider::make()->getBreadcrumbs($routeParameters['paymentServiceProvider']),
                $headCrumb(
                    $paymentAccount,
                    [
                        'index' => [
                            'name'       => 'grp.org.accounting.org-payment-service-providers.show.payment-accounts.index',
                            'parameters' => $routeParameters
                        ],
                        'model' => [
                            'name'       => 'grp.org.accounting.org-payment-service-providers.show.payment-accounts.show',
                            'parameters' => $routeParameters
                        ]
                    ],
                    $suffix
                )
            ),
            default => []
        };
    }

    public function getPrevious(PaymentAccount $paymentAccount, ActionRequest $request): ?array
    {
        $previous = PaymentAccount::where('code', '<', $paymentAccount->code)->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());
    }

    public function getNext(PaymentAccount $paymentAccount, ActionRequest $request): ?array
    {
        $next = PaymentAccount::where('code', '>', $paymentAccount->code)->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?PaymentAccount $paymentAccount, string $routeName): ?array
    {
        if (!$paymentAccount) {
            return null;
        }

        return match ($routeName) {
            'grp.org.accounting.payment-accounts.show' => [
                'label' => $paymentAccount->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'   => $paymentAccount->organisation->slug,
                        'paymentAccount' => $paymentAccount->slug
                    ]

                ]
            ],
            'grp.org.accounting.org-payment-service-providers.show.payment-accounts.show' => [
                'label' => $paymentAccount->name,
                'route' => [
                    'name'       => $routeName,
                    'parameters' => [
                        'organisation'           => $paymentAccount->organisation->slug,
                        'paymentServiceProvider' => $paymentAccount->paymentServiceProvider->slug,
                        'paymentAccount'         => $paymentAccount->slug
                    ]

                ]
            ],
            default => null
        };
    }
}
