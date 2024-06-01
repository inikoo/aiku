<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\UI;

use App\Actions\InertiaAction;
use App\Models\Accounting\PaymentAccount;
use App\Models\Helpers\Currency;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class CreatePayment extends InertiaAction
{
    public function htmlResponse(ActionRequest $request): Response
    {
        return Inertia::render(
            'CreateModel',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->getName(), array_values($request->route()->originalParameters())),
                'title'       => __('new payment'),
                'pageHead'    => [
                    'title'        => __('new payment'),
                    'cancelCreate' => [
                        'route' => [
                            'name' => match ($request->route()->getName()) {
                                'grp.org.accounting.payment-accounts.show.payments.create' => 'grp.org.accounting.payment-accounts.show',
                                default                                                    => preg_replace('/create$/', 'index', $request->route()->getName())
                            },
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ]
                ],
                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('payment'),
                            'fields' => [
                                'reference' => [
                                    'type'  => 'input',
                                    'label' => __('reference'),
                                    'value' => ''
                                ],
                                'customer' => [
                                    'type'  => 'select',
                                    'label' => __('customer'),
                                    'value' => ''
                                ]
                            ]
                        ],
                        [
                            'title'  => __('Details'),
                            'fields' => [
                                'amount' => [
                                    'type'  => 'input',
                                    'label' => __('amount'),
                                    'value' => ''
                                ],
                                'group_amount' => [
                                    'type'  => 'input',
                                    'label' => __('group currency amount'),
                                    'value' => ''
                                ],
                                'org_amount' => [
                                    'type'  => 'input',
                                    'label' => __('organisation currency amount'),
                                    'value' => ''
                                ],
                                'currency_id' => [
                                    'type'    => 'currency',
                                    'label'   => __('currency'),
                                    'value'   => '',
                                    'options' => Currency::get()->pluck('code')
                                ],
                                'date' => [
                                    'type'  => 'date',
                                    'label' => __('date'),
                                    'value' => ''
                                ]
                            ]
                        ]
                    ],
                    'route' => [
                        'name' => 'grp.models.payment-account.store'
                    ]
                ],
            ]
        );
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo('accounting.edit');
    }


    public function asController(ActionRequest $request): void
    {
        $this->initialisation($request);

    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentAccount(PaymentAccount $paymentAccount, ActionRequest $request): void
    {
        $this->initialisation($request);
    }

    public function getBreadcrumbs(string $routeName, array $params): array
    {
        return array_merge(
            IndexPayments::make()->getBreadcrumbs($routeName, $params),
            [
                [
                    'type'          => 'creatingModel',
                    'creatingModel' => [
                        'label' => __("creating payment"),
                    ]
                ]
            ]
        );
    }
}
