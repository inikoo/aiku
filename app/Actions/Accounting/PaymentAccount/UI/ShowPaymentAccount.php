<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 15:40:56 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\PaymentAccount\UI;

use App\Actions\Accounting\Payment\UI\IndexPayments;
use App\Actions\InertiaAction;
use App\Enums\UI\PaymentAccountTabsEnum;
use App\Http\Resources\Accounting\PaymentAccountResource;
use App\Http\Resources\Accounting\PaymentResource;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Marketing\Shop;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

/**
 * @property PaymentAccount $paymentAccount
 */
class ShowPaymentAccount extends InertiaAction
{
    use HasUIPaymentAccount;
    public function handle(PaymentAccount $paymentAccount): PaymentAccount
    {
        return $paymentAccount;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('accounting.edit');
        return $request->user()->hasPermissionTo("accounting.view");
    }

    public function inTenant(PaymentAccount $paymentAccount, ActionRequest $request): PaymentAccount
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request)->withTab(PaymentAccountTabsEnum::values());
        return $this->handle($paymentAccount);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, PaymentAccount $paymentAccount, ActionRequest $request): PaymentAccount
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request)->withTab(PaymentAccountTabsEnum::values());
        return $this->handle($paymentAccount);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, PaymentAccount $paymentAccount, ActionRequest $request): PaymentAccount
    {
        $this->initialisation($request);
        return $this->handle($paymentAccount);
    }

    public function htmlResponse(PaymentAccount $paymentAccount, ActionRequest $request): Response
    {
        return Inertia::render(
            'Accounting/PaymentAccount',
            [
                'title'       => $paymentAccount->name,
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $paymentAccount),
                'navigation'                            => [
                    'previous' => $this->getPrevious($paymentAccount, $request),
                    'next'     => $this->getNext($paymentAccount, $request),
                ],
                'pageHead'    => [
                    'icon'    => 'fal fa-agent',
                    'title'   => $paymentAccount->slug,
                    'create'  => $this->canEdit && (
                        $this->routeName=='accounting.payment-service-providers.show.payment-accounts.show' or
                        $this->routeName=='accounting.payment-accounts.show'
                    ) ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'show.payments.create', $this->routeName) ,
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('payment')
                    ] : false,
                    'meta'  => [
                        [
                            'name'   => trans_choice('payment | payments', $paymentAccount->stats->number_payments),
                            'number' => $paymentAccount->stats->number_payments,
                            'href'   => match ($this->routeName) {
                                'accounting.payment-service-providers.show.payment-accounts.show' => [
                                    'accounting.payment-service-providers.show.payment-accounts.show.payments.index',
                                    [$paymentAccount->paymentServiceProvider->slug, $paymentAccount->slug]
                                ],
                                default => [
                                    'accounting.payment-accounts.show.payments.index',
                                    $paymentAccount->slug
                                ]
                            },
                            'leftIcon' => [
                                'icon'    => 'fal fa-credit-card',
                                'tooltip' => __('payments')
                            ]
                        ],

                    ],

                ],
                'tabs'=> [
                    'current'    => $this->tab,
                    'navigation' => PaymentAccountTabsEnum::navigation()

                ],

                PaymentAccountTabsEnum::PAYMENTS->value => $this->tab == PaymentAccountTabsEnum::PAYMENTS->value ?
                    fn () => PaymentResource::collection(IndexPayments::run($this->paymentAccount))
                    : Inertia::lazy(fn () => PaymentResource::collection(IndexPayments::run($this->paymentAccount))),



            ]
        )->table(IndexPayments::make()->tableStructure());
    }


    public function jsonResponse(PaymentAccount $paymentAccount): PaymentAccountResource
    {
        return new PaymentAccountResource($paymentAccount);
    }

    public function getPrevious(PaymentAccount $paymentAccount, ActionRequest $request): ?array
    {
        $previous=PaymentAccount::where('code', '<', $paymentAccount->code)->when(true, function ($query) use ($paymentAccount, $request) {
            if ($request->route()->getName() == 'accounting.payment-service-providers.show.payment-accounts.show') {
                $query->where('payment_accounts.payment_service_provider_id', $paymentAccount->payment_service_provider_id);
            }
        })->orderBy('code', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(PaymentAccount $paymentAccount, ActionRequest $request): ?array
    {
        $next = PaymentAccount::where('code', '>', $paymentAccount->code)->when(true, function ($query) use ($paymentAccount, $request) {
            if ($request->route()->getName() == 'accounting.payment-service-providers.show.payment-accounts.show') {
                $query->where('payment_accounts.payment_service_provider_id', $paymentAccount->payment_service_provider_id);
            }
        })->orderBy('code')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?PaymentAccount $paymentAccount, string $routeName): ?array
    {
        if(!$paymentAccount) {
            return null;
        }
        return match ($routeName) {
            'accounting.payment-accounts.show'=> [
                'label'=> $paymentAccount->code,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'account'  => $paymentAccount->slug
                    ]

                ]
            ],
            'accounting.payment-service-providers.show.payment-accounts.show' => [
                'label'=> $paymentAccount->code,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'provider' => $paymentAccount->payment_service_provider_id,
                        'account' => $paymentAccount->code
                    ]

                ]
            ],

        };
    }
}
