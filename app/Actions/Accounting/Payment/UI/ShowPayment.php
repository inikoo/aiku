<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 08:17:53 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\DepartmentTabsEnum;
use App\Enums\UI\PaymentTabsEnum;
use App\Http\Resources\Accounting\PaymentResource;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Marketing\Shop;
use App\Models\Sales\Order;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property Payment $payment
 */
class ShowPayment extends InertiaAction
{
    use HasUIPayment;
    public function handle(Payment $payment): Payment
    {
        return $payment;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('accounting.edit');
        return $request->user()->hasPermissionTo("accounting.view");
    }

    public function inTenant(Payment $payment, ActionRequest $request): Payment
    {
        $this->initialisation($request)->withTab(PaymentTabsEnum::values());
        return $this->handle($payment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentAccount(PaymentAccount $paymentAccount, Payment $payment, ActionRequest $request): Payment
    {
        $this->initialisation($request)->withTab(PaymentTabsEnum::values());
        return $this->handle($payment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentAccountInShop(Shop $shop, PaymentAccount $paymentAccount, Payment $payment, ActionRequest $request): Payment
    {
        $this->initialisation($request)->withTab(PaymentTabsEnum::values());
        return $this->handle($payment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentAccountInPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, PaymentAccount $paymentAccount, Payment $payment, ActionRequest $request): Payment
    {
        $this->initialisation($request)->withTab(PaymentTabsEnum::values());
        return $this->handle($payment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, Payment $payment, ActionRequest $request): Payment
    {
        $this->initialisation($request)->withTab(PaymentTabsEnum::values());
        return $this->handle($payment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inOrderInShop(Shop $shop, Order $order, Payment $payment, ActionRequest $request): Payment
    {
        $this->initialisation($request)->withTab(PaymentTabsEnum::values());
        return $this->handle($payment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inOrder(Order $order, Payment $payment, ActionRequest $request): Payment
    {
        $this->initialisation($request)->withTab(PaymentTabsEnum::values());
        return $this->handle($payment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, Payment $payment, ActionRequest $request): Payment
    {
        $this->initialisation($request)->withTab(DepartmentTabsEnum::values());

        return $this->handle($payment);
    }

    public function htmlResponse(Payment $payment, ActionRequest $request): Response
    {
        return Inertia::render(
            'Accounting/Payment',
            [
                'title'       => __($payment->id),
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $request->route()->parameters),
                'navigation'                            => [
                    'previous' => $this->getPrevious($payment, $request),
                    'next'     => $this->getNext($payment, $request),
                ],
                'pageHead'    => [
                    'icon'  => 'fal fa-coins',
                    'title' => $payment->slug,
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ] : false,

                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => PaymentTabsEnum::navigation()
                ],
            ]
        );
    }


    public function jsonResponse(Payment $payment): PaymentResource
    {
        return new PaymentResource($payment);
    }

    public function getPrevious(Payment $payment, ActionRequest $request): ?array
    {
        $previous=Payment::where('reference', '<', $payment->reference)->when(true, function ($query) use ($payment, $request) {
            switch ($request->route()->getName()) {
                case 'accounting.payment-accounts.show.payments.show':
                    $query->where('payments.payment_account_id', $payment->payment_account_id);
                    break;
                case 'accounting.payment-service-providers.show.payment-accounts.show.payments.show':
                case 'accounting.payment-service-providers.show.payments.show':
                    $query->where('payment_accounts.payment_account_id', $payment->paymentAccount->payment_service_provider_id);
                    break;

            }
        })->orderBy('reference', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(Payment $payment, ActionRequest $request): ?array
    {
        $next=Payment::where('reference', '>', $payment->reference)->when(true, function ($query) use ($payment, $request) {
            switch ($request->route()->getName()) {
                case 'accounting.payment-accounts.show.payments.show':
                    $query->where('payments.payment_account_id', $payment->paymentAccount->id);
                    break;
                case 'accounting.payment-service-providers.show.payment-accounts.show.payments.show':
                case 'accounting.payment-service-providers.show.payments.show':
                    $query->where('payment_accounts.payment_account_id', $payment->paymentAccount->payment_service_provider_id);
                    break;

            }
        })->orderBy('reference')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?Payment $payment, string $routeName): ?array
    {
        if(!$payment) {
            return null;
        }
        return match ($routeName) {
            'accounting.payments.show'=> [
                'label'=> $payment->reference,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'payment'  => $payment->slug
                    ]

                ]
            ],
            'accounting.payment-accounts.show.payments.show' => [
                'label'=> $payment->reference,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'paymentAccount'=> $payment->paymentAccount->slug,
                        'payment'       => $payment->slug
                    ]

                ]
            ],
            'accounting.payment-service-providers.show.payments.show'=> [
                'label'=> $payment->reference,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'paymentServiceProvider'=> $payment->paymentAccount->paymentServiceProvider->slug,
                        'payment'               => $payment->slug
                    ]

                ]
            ],
            'accounting.payment-service-providers.show.payment-accounts.show.payments.show' => [
                'label'=> $payment->reference,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'paymentServiceProvider'=> $payment->paymentAccount->paymentServiceProvider->slug,
                        'paymentAccount'        => $payment->paymentAccount->slug,
                        'payment'               => $payment->slug
                    ]

                ]
            ]
        };
    }
}
