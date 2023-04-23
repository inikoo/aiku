<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\UI;

use App\Actions\InertiaAction;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreatePayment extends InertiaAction
{
    private Shop|Tenant|PaymentServiceProvider|PaymentAccount $parent;

    public function handle(): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('new payment'),
                'pageHead'    => [
                    'title'        => __('new payment'),
                    'cancelCreate' => [
                        'route' => [
                            'name' => match ($this->routeName) {
                                'accounting.payment-accounts.show.payments.create' => 'accounting.payment-accounts.show',
                                default                                            => preg_replace('/create$/', 'index', $this->routeName)
                            },
                            'parameters' => array_values($this->originalParameters)
                        ]
                    ]
                ],
            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->can('accounting.edit');
    }


    public function asController(ActionRequest $request): Response
    {
        $this->initialisation($request);
        $this->parent = app('currentTenant');
        return $this->handle();
    }

    public function inPaymentAccount(PaymentAccount $paymentAccount, ActionRequest $request): Response
    {
        $this->initialisation($request);
        $this->parent = $paymentAccount;
        return $this->handle();
    }

    public function getBreadcrumbs(string $routeName, Shop|Tenant|PaymentServiceProvider|PaymentAccount $parent): array
    {
        return array_merge(
            IndexPayments::make()->getBreadcrumbs($routeName, $parent),
            [
                [
                    'suffix'=> __('creating payment')
                ]
            ]
        );
    }
}
