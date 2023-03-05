<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 26 Oct 2022 13:06:04 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Actions\Accounting\PaymentAccount\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\Accounting\ShowAccountingDashboard;
use App\Actions\InertiaAction;
use App\Http\Resources\Accounting\PaymentResource;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Payment $payment
 */
class ShowPayment extends InertiaAction
{
    public function handle(Payment $payment): Payment
    {
        return $payment;
    }
    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("accounting.view");
    }

    public function asController(Payment $payment, Request $request): Payment
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($payment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentAccount(PaymentAccount $paymentAccount, Payment $payment, Request $request): Payment
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($payment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentAccountInPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, PaymentAccount $paymentAccount, Payment $payment, Request $request): Payment
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($payment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, Payment $payment, Request $request): Payment
    {
        $this->routeName = $request->route()->getName();
        $this->validateAttributes();

        return $this->handle($payment);
    }

    public function htmlResponse(Payment $payment): Response
    {
        $this->validateAttributes();


        return Inertia::render(
            'Accounting/Payment',
            [
                'title'       => __($payment->id),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $payment),
                'pageHead'    => [
                    'icon'  => 'fal fa-coins',
                    'title' => $payment->slug,


                ],
                'payment' => $payment
            ]
        );
    }


    #[Pure] public function jsonResponse(Payment $payment): PaymentResource
    {
        return new PaymentResource($payment);
    }


    public function getBreadcrumbs(string $routeName, Payment $payment): array
    {
        $headCrumb = function (array $routeParameters = []) use ($payment, $routeName) {
            $indexRouteParameters = $routeParameters;
            array_pop($indexRouteParameters);

            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'name'            => $payment->slug,
                    'index'           => [
                        'route'           => preg_replace('/show$/', 'index', $routeName),
                        'routeParameters' => $indexRouteParameters,
                        'overlay'         => __('payments list')
                    ],
                    'modelLabel' => [
                        'label' => __('payment')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'accounting.payments.show' => array_merge(
                (new ShowaccountingDashboard())->getBreadcrumbs(),
                $headCrumb([$payment->slug])
            ),
            'accounting.payment-service-provider.show.payments.show' => array_merge(
                (new ShowPaymentServiceProvider())
                    ->getBreadcrumbs($payment->paymentAccount->paymentServiceProvider),
                $headCrumb([$payment->paymentAccount->paymentServiceProvider->slug, $payment->slug])
            ),
            'accounting.payment-accounts.show.payments.show' => array_merge(
                (new ShowPaymentAccount())
                    ->getBreadcrumbs('accounting.payment-accounts.show', $payment->paymentAccount),
                $headCrumb([$payment->paymentAccount->slug, $payment->slug])
            ),
            'accounting.payment-service-provider.show.payment-accounts.show.payments.show' => array_merge(
                (new ShowPaymentAccount())
                    ->getBreadcrumbs('accounting.payment-service-provider.show.payment-accounts.show', $payment->paymentAccount),
                $headCrumb(
                    [
                        $payment->paymentAccount->paymentServiceProvider->slug, $payment->paymentAccount->slug, $payment->slug
                    ]
                )
            ),

            default => []
        };
    }
}
