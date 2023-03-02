<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 13:06:04 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount;

use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\Accounting\ShowAccountingDashboard;
use App\Actions\InertiaAction;
use App\Http\Resources\Accounting\PaymentAccountResource;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;


/**
 * @property PaymentAccount $paymentAccount
 */
class ShowPaymentAccount extends InertiaAction
{
    public function handle(PaymentAccount $paymentAccount): PaymentAccount
    {
        return $paymentAccount;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("accounting.view");
    }

    public function asController(PaymentAccount $paymentAccount, Request $request): PaymentAccount
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($paymentAccount);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, PaymentAccount $paymentAccount, Request $request): PaymentAccount
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($paymentAccount);
    }

    public function htmlResponse(PaymentAccount $paymentAccount): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Accounting/PaymentAccount',
            [
                'title' => $paymentAccount->name,
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $paymentAccount),
                'pageHead' => [
                    'icon' => 'fal fa-agent',
                    'title' => $paymentAccount->slug,
                    'meta' => [
                        [
                            'name' => trans_choice('payment | payments', $paymentAccount->stats->number_payments),
                            'number' => $paymentAccount->stats->number_payments,
                            'href' => match ($this->routeName) {
                                'accounting.payment-service-providers.show.payment-accounts.show' => [
                                    'accounting.payment-service-providers.show.payment-accounts.show.payments.index',
                                    [$paymentAccount->paymentServiceProvider->slug, $paymentAccount->slug]
                                ],
                                default => [
                                    'accounting.payment-accounts.show.payments.index',
                                    $paymentAccount->slug
                                ]
                            },
                            'leftIcon' => [
                                'icon' => 'fal fa-credit-card',
                                'tooltip' => __('payments')
                            ]
                        ],

                    ]

                ],
                'payment_account' => $paymentAccount
            ]
        );
    }


    public function jsonResponse(PaymentAccount $paymentAccount): PaymentAccountResource
    {
        return new PaymentAccountResource($paymentAccount);
    }


    public function getBreadcrumbs(string $routeName, PaymentAccount $paymentAccount): array
    {
        $headCrumb = function (array $routeParameters = []) use ($paymentAccount, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route' => $routeName,
                    'routeParameters' => $routeParameters,
                    'name' => $paymentAccount->code,
                    'index' => [
                        'route' => preg_replace('/show$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay' => __('accounts list')
                    ],
                    'modelLabel' => [
                        'label' => __('account')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'accounting.payment-accounts.show' => array_merge(
                (new ShowAccountingDashboard())->getBreadcrumbs(),
                $headCrumb([$paymentAccount->slug])

            ),
            'accounting.payment-service-providers.show.payment-accounts.show' => array_merge(
                (new ShowPaymentServiceProvider())->getBreadcrumbs($paymentAccount->paymentServiceProvider),
                $headCrumb([$paymentAccount->paymentServiceProvider->slug, $paymentAccount->slug])
            ),
            default => []
        };
    }
}
