<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\UI;

use App\Actions\Accounting\PaymentAccount\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\UI\Accounting\AccountingDashboard;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;

trait HasUIPayments
{
    public function getBreadcrumbs(string $routeName, Shop|Tenant|PaymentServiceProvider|PaymentAccount $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('payments')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'accounting.payments.index' =>
            array_merge(
                (new AccountingDashboard())->getBreadcrumbs(),
                $headCrumb()
            ),
            'accounting.payment-service-providers.show.payments.index' =>
            array_merge(
                (new ShowPaymentServiceProvider())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            'accounting.payment-service-providers.show.payment-accounts.show.payments.index' =>
            array_merge(
                (new ShowPaymentAccount())->getBreadcrumbs('accounting.payment-service-providers.show.payment-accounts.show', $parent),
                $headCrumb([$parent->paymentServiceProvider->slug,$parent->slug])
            ),

            'accounting.payment-accounts.show.payments.index' =>
            array_merge(
                (new ShowPaymentAccount())->getBreadcrumbs('accounting.payment-accounts.show', $parent),
                $headCrumb([$parent->slug])
            ),

            default => []
        };
    }
}
