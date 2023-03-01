<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 13:06:04 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentAccount;

use App\Actions\Accounting\ShowAccountingDashboard;
use App\Actions\InertiaAction;
use App\Http\Resources\Accounting\PaymentAccountResource;
use App\Models\Accounting\PaymentAccount;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
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
                'title'       => __('payment_account'),
                'breadcrumbs' => $this->getBreadcrumbs($this->paymentAccount),
                'pageHead'    => [
                    'icon'  => 'fal fa-agent',
                    'title' => $this->paymentAccount->slug,
                    'meta'  => [
                        [
                            'name'     => trans_choice('Account | Accounts', $this->paymentAccount->stats->number_payments),
                            'number'   => $this->paymentAccount->stats->number_payments,
                            'href'     => [
                                'accounting.accounts.index',
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


    #[Pure] public function jsonResponse(): PaymentAccountResource
    {
        return new PaymentAccountResource($this->paymentAccount);
    }


    public function getBreadcrumbs(PaymentAccount $paymentAccount): array
    {
        return array_merge(
            (new ShowAccountingDashboard())->getBreadcrumbs(),
            [
                'accounting.accounts.show' => [
                    'route'           => 'accounting.accounts.show',
                    'routeParameters' => $paymentAccount->slug,
                    'name'            => $paymentAccount->code,
                    'index'           => [
                        'route'   => 'accounting.accounts.index',
                        'overlay' => __('Payment Account List')
                    ],
                    'modelLabel'      => [
                        'label' => __('Payment Account')
                    ],
                ],
            ]
        );
    }

}
