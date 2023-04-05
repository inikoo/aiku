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

/**
 * @property PaymentServiceProvider $paymentServiceProvider
 */
class ShowPaymentServiceProvider extends InertiaAction
{
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("accounting.view");
    }

    public function asController(PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): void
    {
        $this->initialisation($request)->withTab(PaymentServiceProviderTabsEnum::values());
        $this->paymentServiceProvider    = $paymentServiceProvider;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Accounting/PaymentServiceProvider',
            [
                'title'       => __('payment service provider'),
                'breadcrumbs' => $this->getBreadcrumbs($this->paymentServiceProvider),
                'pageHead'    => [
                    'icon'  => 'fal fa-cash-register',
                    'title' => $this->paymentServiceProvider->slug,
                    'meta'  => [
                        [
                            'name'     => trans_choice('account | accounts', $this->paymentServiceProvider->stats->number_accounts),
                            'number'   => $this->paymentServiceProvider->stats->number_accounts,
                            'href'     => [
                                'accounting.payment-service-providers.show.payment-accounts.index',
                                $this->paymentServiceProvider->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-money-check-alt',
                                'tooltip' => __('accounts')
                            ]
                        ],
                        [
                            'name'     => trans_choice('payment | payments', $this->paymentServiceProvider->stats->number_payments),
                            'number'   => $this->paymentServiceProvider->stats->number_payments,
                            'href'     => [
                                'accounting.payment-service-providers.show.payments.index',
                                $this->paymentServiceProvider->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-credit-card',
                                'tooltip' => __('payments')
                            ]
                        ]

                    ]

                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => PaymentServiceProviderTabsEnum::navigation()

                ],

                PaymentServiceProviderTabsEnum::PAYMENT_ACCOUNTS->value => $this->tab == PaymentServiceProviderTabsEnum::PAYMENT_ACCOUNTS->value ?
                    fn () => PaymentAccountResource::collection(IndexPaymentAccounts::run($this->paymentServiceProvider))
                    : Inertia::lazy(fn () => PaymentAccountResource::collection(IndexPaymentAccounts::run($this->paymentServiceProvider))),

                PaymentServiceProviderTabsEnum::PAYMENTS->value => $this->tab == PaymentServiceProviderTabsEnum::PAYMENTS->value ?
                    fn () => PaymentResource::collection(IndexPayments::run($this->paymentServiceProvider))
                    : Inertia::lazy(fn () => PaymentResource::collection(IndexPayments::run($this->paymentServiceProvider))),
            ]
        )->table(IndexPaymentAccounts::make()->tableStructure())
            ->table(IndexPayments::make()->tableStructure());
    }


    public function jsonResponse(): PaymentServiceProviderResource
    {
        return new PaymentServiceProviderResource($this->paymentServiceProvider);
    }


    public function getBreadcrumbs(PaymentServiceProvider $paymentServiceProvider, $suffix=null): array
    {
        return array_merge(
            (new AccountingDashboard())->getBreadcrumbs(),
            [
                 [

                     'type'      => 'indexModel',
                     'indexModel'=> [
                         'index'=> [
                             'route'=> [
                                 'name'=> 'accounting.payment-service-providers.index',
                             ],
                             'label'=> __('providers')
                         ],
                         'model'=> [
                             'route'=> [
                                 'name'      => 'accounting.payment-service-providers.show',
                                 'parameters'=> [$paymentServiceProvider->slug]
                             ],
                             'label'=> $paymentServiceProvider->code,
                         ],
                     ],
                     'suffix'=> $suffix,
                ],
            ]
        );
    }
}
