<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 13:06:04 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */
namespace App\Actions\Accounting\PaymentServiceProvider;
use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\Accounting\PaymentAccount\UI\IndexPaymentAccounts;
use App\Actions\InertiaAction;
use App\Actions\UI\Accounting\AccountingDashboard;
use App\Enums\UI\PaymentServiceProviderTabsEnum;
use App\Http\Resources\Accounting\PaymentAccountResource;
use App\Http\Resources\Accounting\PaymentResource;
use App\Http\Resources\Accounting\PaymentServiceProviderResource;
use App\Models\Accounting\PaymentServiceProvider;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
class ShowPaymentServiceProvider extends InertiaAction
{
    public function handle(PaymentServiceProvider $paymentServiceProvider): PaymentServiceProvider
    {
        return $paymentServiceProvider;
    }
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("accounting.view");
    }
    public function asController(PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): PaymentServiceProvider
    {
        $this->initialisation($request)->withTab(PaymentServiceProviderTabsEnum::values());
        return $this->handle($paymentServiceProvider);
    }
    public function htmlResponse(PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): Response
    {
        return Inertia::render(
            'Accounting/PaymentServiceProvider',
            [
                'title'       => __('payment service provider'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->parameters
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($paymentServiceProvider, $request),
                    'next'     => $this->getNext($paymentServiceProvider, $request),
                ],
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-cash-register'],
                            'title' => __('payment service provider')
                        ],
                    'title' => $paymentServiceProvider->slug,
                    'meta'  => [
                        [
                            'name'     => trans_choice('account | accounts', $paymentServiceProvider->stats->number_accounts),
                            'number'   => $paymentServiceProvider->stats->number_accounts,
                            'href'     => [
                                'accounting.payment-service-providers.show.payment-accounts.index',
                                $paymentServiceProvider->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-money-check-alt',
                                'tooltip' => __('accounts')
                            ]
                        ],
                        [
                            'name'     => trans_choice('payment | payments', $paymentServiceProvider->stats->number_payments),
                            'number'   => $paymentServiceProvider->stats->number_payments,
                            'href'     => [
                                'accounting.payment-service-providers.show.payments.index',
                                $paymentServiceProvider->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-coins',
                                'tooltip' => __('payments')
                            ]
                        ]
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => PaymentServiceProviderTabsEnum::navigation()
                ],
                PaymentServiceProviderTabsEnum::PAYMENT_ACCOUNTS->value => $this->tab == PaymentServiceProviderTabsEnum::PAYMENT_ACCOUNTS->value ?
                    fn () => PaymentAccountResource::collection(IndexPaymentAccounts::run($paymentServiceProvider))
                    : Inertia::lazy(fn () => PaymentAccountResource::collection(IndexPaymentAccounts::run($paymentServiceProvider))),
                PaymentServiceProviderTabsEnum::PAYMENTS->value => $this->tab == PaymentServiceProviderTabsEnum::PAYMENTS->value ?
                    fn () => PaymentResource::collection(IndexPayments::run($paymentServiceProvider))
                    : Inertia::lazy(fn () => PaymentResource::collection(IndexPayments::run($paymentServiceProvider))),
            ]
        )->table(IndexPaymentAccounts::make()->tableStructure())
            ->table(IndexPayments::make()->tableStructure());
    }
    public function jsonResponse(PaymentServiceProvider $paymentServiceProvider): PaymentServiceProviderResource
    {
        return new PaymentServiceProviderResource($paymentServiceProvider);
    }
    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        return array_merge(
            AccountingDashboard::make()->getBreadcrumbs('accounting.dashboard', []),
            [
                [
                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => [
                                'name' => 'accounting.payment-service-providers.index',
                            ],
                            'label' => __('providers')
                        ],
                        'model' => [
                            'route' => [
                                'name'       => 'accounting.payment-service-providers.show',
                                'parameters' => $routeParameters
                            ],
                            'label' => $routeParameters['paymentServiceProvider']->code,
                        ],
                    ],
                    'suffix'         => $suffix,
                ],
            ]
        );
    }

    public function getPrevious(PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): ?array
    {
        $previous = PaymentServiceProvider::where('code', '<', $paymentServiceProvider->code)->orderBy('code', 'desc')->first();
        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): ?array
    {
        $next = PaymentServiceProvider::where('code', '>', $paymentServiceProvider->code)->orderBy('code')->first();
        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?PaymentServiceProvider $paymentServiceProvider, string $routeName): ?array
    {
        if(!$paymentServiceProvider) {
            return null;
        }
        return match ($routeName) {
            'accounting.payment-service-providers.show'=> [
                'label'=> $paymentServiceProvider->code,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'paymentServiceProvider'=> $paymentServiceProvider->slug
                    ]

                ]
            ]
        };
    }
}
