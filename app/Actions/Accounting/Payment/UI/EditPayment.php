<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\UI;

use App\Actions\InertiaAction;
use App\Enums\UI\Accounting\PaymentTabsEnum;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Ordering\Order;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditPayment extends InertiaAction
{
    use HasUIPayment;
    public function handle(Payment $payment): Payment
    {
        return $payment;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('accounting.edit');
        return $request->user()->hasPermissionTo("accounting.view");
    }

    public function inOrganisation(Payment $payment, ActionRequest $request): Payment
    {
        $this->initialisation($request);
        return $this->handle($payment);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, Payment $payment, ActionRequest $request): Payment
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
            'EditModel',
            [
                'title'       => __('payment'),
                'breadcrumbs' => ShowPayment::make()->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()),
                'pageHead'    => [
                    'title'     => $payment->reference,
                    'actions'  => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $request->route()->getName()),
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ]
                    ],


                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('id'),
                            'fields' => [
                                'amount' => [
                                    'type'  => 'input',
                                    'label' => __('amount'),
                                    'value' => $payment->amount
                                ],
                                'date' => [
                                    'type'  => 'input',
                                    'label' => __('label'),
                                    'value' => $payment->date
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'grp.models.payment.update',
                            'parameters'=> $payment->id

                        ],
                    ]
                ]
            ]
        );
    }
}
