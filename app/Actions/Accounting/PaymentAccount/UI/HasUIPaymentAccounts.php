<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\PaymentAccount\UI;

use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\UI\Accounting\AccountingDashboard;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;

trait HasUIPaymentAccounts
{
    public function getBreadcrumbs(string $routeName, Shop|Tenant|PaymentServiceProvider $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel'      => [
                        'label' => __('accounts')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'accounting.payment-accounts.index' =>
            array_merge(
                (new AccountingDashboard())->getBreadcrumbs(),
                $headCrumb()
            ),
            'accounting.payment-service-providers.show.payment-accounts.index' =>
            array_merge(
                (new ShowPaymentServiceProvider())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }
}
