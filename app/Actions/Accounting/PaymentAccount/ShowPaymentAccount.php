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
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;


/**
 * @property PaymentAccount $paymentAccount
 */
class ShowPaymentAccount extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("accounting.view");
    }

    public function asController(PaymentAccount $paymentAccount): void
    {
        $this->paymentAccount    = $paymentAccount;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Accounting/PaymentAccount',
            [
                'title'       => $this->paymentAccount->name,
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->paymentAccount),
                'pageHead'    => [
                    'icon'  => 'fal fa-agent',
                    'title' => $this->paymentAccount->slug,
                    'meta'  => [
                        [
                            'name'     => trans_choice('Account | Accounts', $this->paymentAccount->stats->number_payments),
                            'number'   => $this->paymentAccount->stats->number_payments,
                            'href'     => [
                                'accounting.payment-accounts.index',
                                $this->paymentAccount->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-map-signs',
                                'tooltip' => __('payment_account')
                            ]
                        ],
                        // TODO ShowSupplierProducts

                    ]

                ],
                'payment_account'   => $this->paymentAccount
            ]
        );
    }


    public function jsonResponse(): PaymentAccountResource
    {
        return new PaymentAccountResource($this->paymentAccount);
    }


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
                    'modelLabel'      => [
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
