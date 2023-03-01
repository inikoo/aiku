<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 13:06:04 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Actions\Accounting\PaymentAccount\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\Accounting\ShowAccountingDashboard;
use App\Actions\InertiaAction;
use App\Http\Resources\Accounting\PaymentResource;
use App\Models\Accounting\Payment;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;


/**
 * @property Payment $payment
 */
class ShowPayment extends InertiaAction
{

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("accounting.view");
    }

    public function asController(Payment $payment): void
    {
        $this->payment = $payment;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Accounting/Payment',
            [
                'title' => __('payment'),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->payment),
                'pageHead' => [
                    'icon' => 'fal fa-agent',
                    'title' => $this->payment->slug,
                    'meta' => [
                        [
                            'name' => trans_choice('Payment | Payments', $this->payment->customer_id),
                            'number' => $this->payment->customer_id,
                            'href' => [
                                'accounting.payments.index',
                                $this->payment->slug
                            ],
                            'leftIcon' => [
                                'icon' => 'fal fa-map-signs',
                                'tooltip' => __('payment')
                            ]
                        ],
                        // TODO ShowSupplierProducts

                    ]

                ],
                'payment' => $this->payment
            ]
        );
    }


    #[Pure] public function jsonResponse(): PaymentResource
    {
        return new PaymentResource($this->payment);
    }


    public function getBreadcrumbs(string $routeName, Payment $payment): array
    {
        $headCrumb = function (array $routeParameters = []) use ($payment, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route' => $routeName,
                    'routeParameters' => $routeParameters,
                    'name' => $payment->slug,
                    'index' => [
                        'route' => preg_replace('/show$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay' => __('payments list')
                    ],
                    'modelLabel' => [
                        'label' => __('payment')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'accounting.payments.show' => array_merge(
                (new ShowaccountingDashboard())->getBreadcrumbs(),
                $headCrumb([$payment->slug])
            ),
            'accounting.payment-service-provider.show.payments.show' => array_merge(
                (new ShowPaymentServiceProvider())
                    ->getBreadcrumbs($payment->paymentAccount->paymentServiceProvider),
                $headCrumb([$payment->paymentAccount->paymentServiceProvider->slug, $payment->slug])
            ),
            'accounting.payment-accounts.show.payments.show' => array_merge(
                (new ShowPaymentAccount())
                    ->getBreadcrumbs('accounting.payment-accounts.show', $payment->paymentAccount),
                $headCrumb([$payment->paymentAccount->slug, $payment->slug])
            ),
            'accounting.payment-service-provider.show.payment-accounts.show.payments.show' => array_merge(
                (new ShowPaymentAccount())
                    ->getBreadcrumbs('accounting.payment-service-provider.show.payment-accounts.show', $payment->paymentAccount),
                $headCrumb(
                    [
                        $payment->paymentAccount->paymentServiceProvider->slug, $payment->paymentAccount->slug, $payment->slug
                    ]
                )
            ),

            default => []
        };
    }

}
