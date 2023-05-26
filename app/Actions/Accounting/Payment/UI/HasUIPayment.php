<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\UI;

use App\Actions\Accounting\PaymentAccount\UI\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\Sales\Order\UI\ShowOrder;
use App\Actions\UI\Accounting\AccountingDashboard;

trait HasUIPayment
{
    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $payment   =$routeParameters['payment'];
        $headCrumb = function (array $parameters = []) use ($routeName, $payment) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $parameters,
                    'name'            => $payment->slug,
                    'index'           =>
                        match ($routeName) {
                            'shops.show.orders.show.payments.show', 'orders.show,payments.show' => null,
                            default => [
                                'route'           => preg_replace('/show$/', 'index', $routeName),
                                'routeParameters' => function () use ($parameters) {
                                    $indexParameters = $parameters;
                                    array_pop($indexParameters);

                                    return $indexParameters;
                                },
                                'overlay'         => __('payments list')
                            ]
                        },


                    'modelLabel' => [
                        'label' => __('payment')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'shops.show.orders.show.payments.show' => array_merge(
                (new ShowOrder())->getBreadcrumbs(
                    'shops.show.orders.show',
                    [
                        'shop'  => $routeParameters['shop'],
                        'order' => $routeParameters['order']
                    ]
                ),
                $headCrumb(
                    [
                        $routeParameters['shop']->slug,
                        $routeParameters['order']->slug,
                        $routeParameters['payment']->slug
                    ]
                )
            ),
            'orders.show,payments.show' => array_merge(
                (new ShowOrder())->getBreadcrumbs(
                    'orders.show',
                    [
                        'order' => $routeParameters['order']
                    ]
                ),
                $headCrumb(
                    [
                        $routeParameters['order']->slug,
                        $routeParameters['payment']->slug
                    ]
                )
            ),
            'accounting.payments.show' => array_merge(
                AccountingDashboard::make()->getBreadcrumbs('accounting.dashboard', []),
                $headCrumb([$routeParameters['payment']->slug])
            ),
            'accounting.payment-service-provider.show.payments.show' => array_merge(
                (new ShowPaymentServiceProvider())
                    ->getBreadcrumbs($routeParameters['payment']->paymentAccount->paymentServiceProvider),
                $headCrumb([$routeParameters['payment']->paymentAccount->paymentServiceProvider->slug, $routeParameters['payment']->slug])
            ),
            'accounting.payment-accounts.show.payments.show' => array_merge(
                (new ShowPaymentAccount())
                    ->getBreadcrumbs('accounting.payment-accounts.show', $routeParameters['payment']->paymentAccount),
                $headCrumb([$routeParameters['payment']->paymentAccount->slug, $routeParameters['payment']->slug])
            ),
            'accounting.payment-service-provider.show.payment-accounts.show.payments.show' => array_merge(
                (new ShowPaymentAccount())
                    ->getBreadcrumbs('accounting.payment-service-provider.show.payment-accounts.show', $routeParameters['payment']->paymentAccount),
                $headCrumb(
                    [
                        $routeParameters['payment']->paymentAccount->paymentServiceProvider->slug,
                        $routeParameters['payment']->paymentAccount->slug,
                        $routeParameters['payment']->slug
                    ]
                )
            ),

            default => []
        };
    }
}
