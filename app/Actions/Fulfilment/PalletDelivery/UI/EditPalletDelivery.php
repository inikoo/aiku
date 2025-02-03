<?php

/*
 * author Arya Permana - Kirin
 * created on 31-01-2025-09h-47m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Fulfilment\PalletDelivery\UI;

use App\Actions\OrgAction;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class EditPalletDelivery extends OrgAction
{
    private Fulfilment|FulfilmentCustomer $parent;

    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {
        return $palletDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->authTo("fulfilment.{$this->fulfilment->id}.view");
    }

    public function inFulfilmentCustomer(
        Organisation $organisation,
        Fulfilment $fulfilment,
        FulfilmentCustomer $fulfilmentCustomer,
        PalletDelivery $palletDelivery,
        ActionRequest $request
    ): PalletDelivery {
        $this->parent = $fulfilmentCustomer;
        $this->initialisationFromFulfilment($fulfilment, $request);


        return $this->handle($palletDelivery);
    }

    public function asController(
        Organisation $organisation,
        Fulfilment $fulfilment,
        PalletDelivery $palletDelivery,
        ActionRequest $request
    ): PalletDelivery {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);


        return $this->handle($palletDelivery);
    }



    public function htmlResponse(PalletDelivery $palletDelivery, ActionRequest $request): Response
    {
        $parent     = $this->parent;
        $container = null;
        if ($parent instanceof FulfilmentCustomer) {
            $container = [
                'icon'    => ['fal', 'fa-user'],
                'tooltip' => __('Customer'),
                'label'   => Str::possessive($parent->customer->name)
            ];
        }

        return Inertia::render(
            'EditModel',
            [
                'title'       => __('pallet delivery'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'pageHead'    => [
                    'title'     => __('Edit Pallet Delivery'),
                    'container' => $container,
                    'meta'      => [
                        [
                            'name' => $palletDelivery->reference
                        ]
                    ],
                    'actions'   => [
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
                            'title'   => __('timeline'),
                            'label'   => __('timeline'),
                            'icon'    => 'fa-light fa-clock',
                            'current' => true,
                            'fields'  => [
                                'received_at' => [
                                    'type'  => 'date',
                                    'label' => __('received date'),
                                    'value' => $palletDelivery->received_at
                                ],
                            ]
                        ],


                    ],
                    'args'      => [
                        'updateRoute' => [
                            'name'       => 'grp.models.pallet-delivery.update',
                            'parameters' => [$palletDelivery->id]

                        ],
                    ]
                ]
            ]
        );
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return ShowPalletDelivery::make()->getBreadcrumbs(
            routeName: preg_replace('/edit$/', 'show', $routeName),
            routeParameters: $routeParameters,
            suffix: '('.__('Editing').')'
        );
    }
}
