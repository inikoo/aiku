<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Tue, 14 Mar 2023 09:31:03 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\PaymentServiceProvider\UI;

use App\Actions\InertiaAction;
use App\Enums\Accounting\PaymentServiceProvider\PaymentServiceProviderTypeEnum;
use App\Models\Accounting\PaymentServiceProvider;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\LaravelOptions\Options;

class EditPaymentServiceProvider extends InertiaAction
{
    public function handle(PaymentServiceProvider $paymentServiceProvider): PaymentServiceProvider
    {
        return $paymentServiceProvider;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("accounting.payment-service-providers.edit");
    }

    public function asController(PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): PaymentServiceProvider
    {
        $this->initialisation($request);
        return $this->handle($paymentServiceProvider);
    }

    /**
     * @throws \Exception
     */
    public function htmlResponse(PaymentServiceProvider $paymentServiceProvider): Response
    {
        return Inertia::render(
            'EditModel',
            [
                'title'       => __('edit payment service provider'),
                'breadcrumbs' => $this->getBreadcrumbs($paymentServiceProvider),
                'pageHead'    => [
                    'title'     => $paymentServiceProvider->code,
                    'actions'   => [
                        [
                            'type'  => 'button',
                            'style' => 'exitEdit',
                            'route' => [
                                'name'       => preg_replace('/edit$/', 'show', $this->routeName),
                                'parameters' => array_values($this->originalParameters)
                            ]
                        ]
                    ]
                ],

                'formData' => [
                    'blueprint' => [
                        [
                            'title'  => __('provider'),
                            'fields' => [
                                'code' => [
                                    'type'     => 'input',
                                    'label'    => __('code'),
                                    'value'    => $paymentServiceProvider->code
                                ],
                                'type' => [
                                    'type'        => 'select',
                                    'label'       => __('type'),
                                    'options'     => Options::forEnum(PaymentServiceProviderTypeEnum::class),
                                    'searchable'  => true,
                                    'placeholder' => __('select a type'),
                                    'value'       => $paymentServiceProvider->type,
                                    'mode'        => 'single'
                                ],
                            ]
                        ]

                    ],
                    'args' => [
                        'updateRoute' => [
                            'name'      => 'models.payment-service-provider.update',
                            'parameters'=> $paymentServiceProvider->slug

                        ],
                    ]
                ]
            ]
        );
    }



    public function getBreadcrumbs(PaymentServiceProvider $paymentServiceProvider): array
    {
        return ShowPaymentServiceProvider::make()->getBreadcrumbs(
            paymentServiceProvider: $paymentServiceProvider,
            suffix: '('.__('editing').')'
        );
    }
}
