<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Mon, 13 Mar 2023 15:06:29 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\PaymentAccount\UI;

use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\UI\Accounting\AccountingDashboard;
use App\Models\Accounting\PaymentAccount;

trait HasUIPaymentAccount
{
    public function getBreadcrumbs(string $routeName, PaymentAccount $paymentAccount): array
    {
        $headCrumb = function (array $routeParameters = []) use ($paymentAccount, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'name'            => $paymentAccount->code,
                    'index'           => [
                        'route'           => preg_replace('/show$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay'         => __('accounts list')
                    ],
                    'modelLabel' => [
                        'label' => __('account')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'accounting.payment-accounts.show' => array_merge(
                (new AccountingDashboard())->getBreadcrumbs(),
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
