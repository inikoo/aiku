<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 08:17:53 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\UI;

use App\Actions\Accounting\PaymentAccount\UI\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\InertiaAction;
use App\Actions\Sales\Order\ShowOrder;
use App\Actions\UI\Accounting\AccountingDashboard;
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
    public function handle(Payment $payment): Payment
    {
        return $payment;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('accounting.edit');
        return $request->user()->hasPermissionTo("accounting.view");
    }

    public function asController(Payment $payment, ActionRequest $request): Payment
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

    public function htmlResponse(Payment $payment, ActionRequest $request): Response
    {
        return Inertia::render(
            'Accounting/Payment',
            [
                'title'       => __($payment->id),
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), $request->route()->parameters),
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

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $payment   =$routeParameters['payment'];
        $headCrumb = function (array $parameters = []) use ($routeName, $payment) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $parameters,
                    'name'            => $payment->slug,
                    'index'           =>
                        match ($routeName) {
                            'shops.show.orders.show.payments.show', 'orders.show,payments.show' => null,
                            default => [
                                'route'           => preg_replace('/show$/', 'index', $routeName),
                                'routeParameters' => function () use ($parameters) {
                                    $indexParameters = $parameters;
                                    array_pop($indexParameters);

                                    return $indexParameters;
                                },
                                'overlay'         => __('payments list')
                            ]
                        },


                    'modelLabel' => [
                        'label' => __('payment')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'shops.show.orders.show.payments.show' => array_merge(
                (new ShowOrder())->getBreadcrumbs(
                    'shops.show.orders.show',
                    [
                        'shop'  => $routeParameters['shop'],
                        'order' => $routeParameters['order']
                    ]
                ),
                $headCrumb(
                    [
                        $routeParameters['shop']->slug,
                        $routeParameters['order']->slug,
                        $routeParameters['payment']->slug
                    ]
                )
            ),
            'orders.show,payments.show' => array_merge(
                (new ShowOrder())->getBreadcrumbs(
                    'orders.show',
                    [
                        'order' => $routeParameters['order']
                    ]
                ),
                $headCrumb(
                    [
                        $routeParameters['order']->slug,
                        $routeParameters['payment']->slug
                    ]
                )
            ),
            'accounting.payments.show' => array_merge(
                (new AccountingDashboard())->getBreadcrumbs(),
                $headCrumb([$routeParameters['payment']->slug])
            ),
            'accounting.payment-service-provider.show.payments.show' => array_merge(
                (new ShowPaymentServiceProvider())
                    ->getBreadcrumbs($routeParameters['payment']->paymentAccount->paymentServiceProvider),
                $headCrumb([$routeParameters['payment']->paymentAccount->paymentServiceProvider->slug, $routeParameters['payment']->slug])
            ),
            'accounting.payment-accounts.show.payments.show' => array_merge(
                (new ShowPaymentAccount())
                    ->getBreadcrumbs('accounting.payment-accounts.show', $routeParameters['payment']->paymentAccount),
                $headCrumb([$routeParameters['payment']->paymentAccount->slug, $routeParameters['payment']->slug])
            ),
            'accounting.payment-service-provider.show.payment-accounts.show.payments.show' => array_merge(
                (new ShowPaymentAccount())
                    ->getBreadcrumbs('accounting.payment-service-provider.show.payment-accounts.show', $routeParameters['payment']->paymentAccount),
                $headCrumb(
                    [
                        $routeParameters['payment']->paymentAccount->paymentServiceProvider->slug,
                        $routeParameters['payment']->paymentAccount->slug,
                        $routeParameters['payment']->slug
                    ]
                )
            ),

            default => []
        };
    }
}
