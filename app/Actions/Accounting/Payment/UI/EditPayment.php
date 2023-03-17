<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Accounting\PaymentResource;
use App\Models\Accounting\Payment;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
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
        $this->canEdit = $request->user()->can('accounting.edit');
        return $request->user()->hasPermissionTo("accounting.view");
    }

    public function asController(Payment $payment, ActionRequest $request): Payment
    {
        $this->initialisation($request);

        return $this->handle($payment);
    }



    public function htmlResponse(Payment $payment): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('payment'),
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $payment),
                'pageHead'    => [
                    'title'     => $payment->reference,
                    'exitEdit'  => [
                        'route' => [
                            'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                            'parameters' => array_values($this->originalParameters)
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
                                    'label' => __('code'),
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
                            'name'      => 'models.payment.update',
                            'parameters'=> $payment->slug

                        ],
                    ]
                ]
            ]
        );
    }

    #[Pure] public function jsonResponse(Payment $payment): PaymentResource
    {
        return new PaymentResource($payment);
    }
}
