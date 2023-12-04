<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Jun 2023 10:58:06 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider\UI;

use App\Actions\InertiaAction;
use App\Models\Accounting\PaymentServiceProvider;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class RemovePaymentServiceProvider extends InertiaAction
{
    public function handle(PaymentServiceProvider $warehouse): PaymentServiceProvider
    {
        return $warehouse;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("inventory.edit");
    }

    public function asController(PaymentServiceProvider $warehouse, ActionRequest $request): PaymentServiceProvider
    {
        $this->initialisation($request);

        return $this->handle($warehouse);
    }


    public function getAction($route): array
    {
        return  [
            'buttonLabel' => __('Delete'),
            'title'       => __('Delete PaymentServiceProvider'),
            'text'        => __("This action will delete this PaymentServiceProvider and its PaymentServiceProvider Areas and Locations"),
            'route'       => $route
        ];
    }

    public function htmlResponse(PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): Response
    {
        return Inertia::render(
            'RemoveModel',
            [
                'title'       => __('delete payment service provider'),
                'breadcrumbs' => $this->getBreadcrumbs($paymentServiceProvider),
                'pageHead'    => [
                    'icon'  =>
                        [
                            'icon'  => ['fal', 'fa-cash-register'],
                            'title' => __('payment service provider')
                        ],
                    'title'  => $paymentServiceProvider->slug,
                    'actions'=> [
                        [
                            'type'  => 'button',
                            'style' => 'cancel',
                            'route' => [
                                'name'       => preg_replace('/remove$/', 'show', $request->route()->getName()),
                                'parameters' => $paymentServiceProvider->slug
                            ]
                        ]
                    ]
                ],
                'data'      => $this->getAction(
                    route:[
                        'name'       => 'grp.models.payment-service-provider.delete',
                        'parameters' => $request->route()->originalParameters()
                    ]
                )
            ]
        );
    }


    public function getBreadcrumbs(PaymentServiceProvider $paymentServiceProvider): array
    {
        return ShowPaymentServiceProvider::make()->getBreadcrumbs($paymentServiceProvider, suffix: '('.__('deleting').')');
    }
}
