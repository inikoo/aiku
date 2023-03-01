<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 13:06:04 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider;

use App\Actions\Accounting\ShowAccountingDashboard;
use App\Actions\InertiaAction;
use App\Http\Resources\Accounting\PaymentServiceProviderResource;
use App\Models\Accounting\PaymentServiceProvider;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
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

    public function asController(PaymentServiceProvider $paymentServiceProvider): void
    {
        $this->paymentServiceProvider    = $paymentServiceProvider;
    }

    public function htmlResponse(): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Accounting/PaymentServiceProvider',
            [
                'title'       => __('payment_service_provider'),
                'breadcrumbs' => $this->getBreadcrumbs($this->paymentServiceProvider),
                'pageHead'    => [
                    'icon'  => 'fal fa-agent',
                    'title' => $this->paymentServiceProvider->slug,
                    'meta'  => [
                        [
                            'name'     => trans_choice('Payment | Payments', $this->paymentServiceProvider->stats->number_payments),
                            'number'   => $this->paymentServiceProvider->stats->number_payments,
                            'href'     => [
                                'accounting.payment-service-providers.index',
                                $this->paymentServiceProvider->slug
                            ],
                            'leftIcon' => [
                                'icon'    => 'fal fa-map-signs',
                                'tooltip' => __('payment_service_provider')
                            ]
                        ],
                        // TODO ShowSupplierProducts

                    ]

                ],
                'payment_service_provider'   => $this->paymentServiceProvider
            ]
        );
    }


    #[Pure] public function jsonResponse(): PaymentServiceProviderResource
    {
        return new PaymentServiceProviderResource($this->paymentServiceProvider);
    }


    public function getBreadcrumbs(PaymentServiceProvider $paymentServiceProvider): array
    {
        return array_merge(
            (new ShowAccountingDashboard())->getBreadcrumbs(),
            [
                'accounting.payment-service-providers.show' => [
                    'route'           => 'accounting.payment-service-providers.show',
                    'routeParameters' => $paymentServiceProvider->slug,
                    'name'            => $paymentServiceProvider->code,
                    'index'           => [
                        'route'   => 'accounting.payment-service-providers.index',
                        'overlay' => __('provider list')
                    ],
                    'modelLabel'      => [
                        'label' => __('provider')
                    ],
                ],
            ]
        );
    }

}
