<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\UI;

use App\Actions\Accounting\PaymentAccount\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\UI\Accounting\AccountingDashboard;
use App\Models\Accounting\Payment;

trait HasUIPayment
{
    public function getBreadcrumbs(string $routeName, Payment $payment): array
    {
        $headCrumb = function (array $routeParameters = []) use ($payment, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'name'            => $payment->slug,
                    'index'           => [
                        'route'           => preg_replace('/show$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay'         => __('payments list')
                    ],
                    'modelLabel' => [
                        'label' => __('payment')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'accounting.payments.show' => array_merge(
                (new AccountingDashboard())->getBreadcrumbs(),
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
