<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 13:06:04 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Actions\Accounting\ShowAccountingDashboard;
use App\Actions\InertiaAction;
use App\Http\Resources\Accounting\PaymentAccountResource;
use App\Http\Resources\Accounting\PaymentResource;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
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
        $this->payment    = $payment;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Accounting/Payment',
            [
                'title'       => __('payment'),
                'breadcrumbs' => $this->getBreadcrumbs($this->payment),
                'pageHead'    => [
                    'icon'  => 'fal fa-agent',
                    'title' => $this->payment->slug,
                    'meta'  => [
                        [
                            'name'     => trans_choice('Payment | Payments', $this->payment->customer_id),
                            'number'   => $this->payment->customer_id,
                            'href'     => [
                                'accounting.payments.index',
                                $this->payment->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-map-signs',
                                'tooltip' => __('payment')
                            ]
                        ],
                        // TODO ShowSupplierProducts

                    ]

                ],
                'payment'   => $this->payment
            ]
        );
    }


    #[Pure] public function jsonResponse(): PaymentResource
    {
        return new PaymentResource($this->payment);
    }


    public function getBreadcrumbs(Payment $payment): array
    {
        return array_merge(
            (new ShowAccountingDashboard())->getBreadcrumbs(),
            [
                'accounting.payments.show' => [
                    'route'           => 'accounting.payments.show',
                    'routeParameters' => $payment->slug,
                    'name'            => $payment->reference,
                    'index'           => [
                        'route'   => 'accounting.payments.index',
                        'overlay' => __('Payment List')
                    ],
                    'modelLabel'      => [
                        'label' => __('Payment')
                    ],
                ],
            ]
        );
    }

}
