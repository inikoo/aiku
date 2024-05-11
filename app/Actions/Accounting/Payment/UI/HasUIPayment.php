<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\UI;

use App\Actions\Accounting\PaymentAccount\UI\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\UI\ShowPaymentServiceProvider;
use App\Actions\Ordering\Order\UI\ShowOrder;
use App\Actions\UI\Accounting\ShowAccountingDashboard;

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
                            'grp.org.shops.show.orders.show.payments.show', 'orders.show,payments.show' => null,
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
            'grp.org.shops.show.orders.show.payments.show' => array_merge(
                (new ShowOrder())->getBreadcrumbs(
                    'grp.org.shops.show.orders.show',
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
                (new \App\Actions\Ordering\Order\UI\ShowOrder())->getBreadcrumbs(
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
            'grp.org.accounting.payments.show' => array_merge(
                ShowAccountingDashboard::make()->getBreadcrumbs('grp.org.accounting.dashboard', []),
                $headCrumb([$routeParameters['payment']->slug])
            ),
            'grp.org.accounting.payment-service-provider.show.payments.show' => array_merge(
                (new ShowPaymentServiceProvider())
                    ->getBreadcrumbs($routeParameters['payment']->paymentAccount->paymentServiceProvider),
                $headCrumb([$routeParameters['payment']->paymentAccount->paymentServiceProvider->slug, $routeParameters['payment']->slug])
            ),
            'grp.org.accounting.payment-accounts.show.payments.show' => array_merge(
                (new ShowPaymentAccount())
                    ->getBreadcrumbs('grp.org.accounting.payment-accounts.show', $routeParameters['payment']->paymentAccount),
                $headCrumb([$routeParameters['payment']->paymentAccount->slug, $routeParameters['payment']->slug])
            ),
            'grp.org.accounting.payment-service-provider.show.payment-accounts.show.payments.show' => array_merge(
                (new ShowPaymentAccount())
                    ->getBreadcrumbs('grp.org.accounting.payment-service-provider.show.payment-accounts.show', $routeParameters['payment']->paymentAccount),
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
