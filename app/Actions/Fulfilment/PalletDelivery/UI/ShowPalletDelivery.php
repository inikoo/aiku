<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 13 Mar 2023 17:41:13 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\PalletDelivery\UI;

use App\Actions\OrgAction;
use App\Enums\UI\PalletDeliveryTabsEnum;
use App\Http\Resources\Fulfilment\PalletDeliveriesResource;
use App\Http\Resources\Fulfilment\PalletDeliveryResource;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\SysAdmin\Organisation;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;

class ShowPalletDelivery extends OrgAction
{
    public function handle(PalletDelivery $palletDelivery): PalletDelivery
    {
        return $palletDelivery;
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.edit");

        return $request->user()->hasPermissionTo("fulfilments.{$this->fulfilment->id}.view");
    }

    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($palletDelivery);
    }

    public function inFulfilmentCustomer(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, PalletDelivery $palletDelivery, ActionRequest $request): PalletDelivery
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($palletDelivery);
    }

    public function htmlResponse(PalletDelivery $palletDelivery, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Fulfilment/PalletDelivery',
            [
                'title'       => __('customer'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->originalParameters()
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($palletDelivery, $request),
                    'next'     => $this->getNext($palletDelivery, $request),
                ],
                'pageHead'    => [
                    'title'        => __($palletDelivery->reference),
                    'icon'         => [
                        'icon'  => ['fal', 'fa-truck'],
                        'title' => __($palletDelivery->reference)
                    ],
                    'edit'  => $this->canEdit ? [
                        'route' => [
                            'name'       => preg_replace('/show$/', 'edit', $request->route()->getName()),
                            'parameters' => array_values($request->route()->originalParameters())
                        ]
                    ] : false,
                    'actions'=> [
                       /* [
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('new delivery'),
                            'label'   => __('create delivery'),
                            'route'   => [
                                'name'       => 'grp.org.fulfilments.show.pallets.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ],*/
                        /*[
                            'type'    => 'button',
                            'style'   => 'create',
                            'tooltip' => __('upload stored items'),
                            'label'   => __('upload stored items'),
                            'route'   => [
                                'name'       => 'grp.fulfilment.stored-items.create', // TODO Create Action for upload CSV/XLSX
                                'parameters' => [$palletDelivery->slug]
                            ]
                        ],*/
                    ]
                ],
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => PalletDeliveryTabsEnum::navigation()
                ],
                PalletDeliveryTabsEnum::PALLETS->value => PalletDeliveryResource::make($palletDelivery)
            ]
        );
    }


    public function jsonResponse(PalletDelivery $palletDelivery): PalletDeliveriesResource
    {
        return new PalletDeliveriesResource($palletDelivery);
    }

    public function getBreadcrumbs(array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'label' => __($routeParameters['parameters']['palletDelivery'])
                    ],
                ],
            ];
        };

        return array_merge(
            IndexPalletDeliveries::make()->getBreadcrumbs(
                $routeParameters
            ),
            $headCrumb(
                [
                    'name'       => 'grp.org.fulfilments.show.pallets.delivery.show',
                    'parameters' => $routeParameters
                ]
            )
        );
    }

    public function getPrevious(PalletDelivery $palletDelivery, ActionRequest $request): ?array
    {
        $previous = PalletDelivery::where('id', '<', $palletDelivery->id)->orderBy('id', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(PalletDelivery $palletDelivery, ActionRequest $request): ?array
    {
        $next = PalletDelivery::where('id', '>', $palletDelivery->id)->orderBy('id')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?PalletDelivery $palletDelivery, string $routeName): ?array
    {
        if(!$palletDelivery) {
            return null;
        }

        return match ($routeName) {
            'grp.org.fulfilments.show.crm.customers.show' ,
            'shops.customers.show'=> [
                'label'=> $palletDelivery->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation'=> $palletDelivery->shop->organisation->slug,
                        'fulfilment'  => $this->fulfilment->slug,
                        'customer'    => $palletDelivery->slug
                    ]

                ]
            ],
            'shops.show.customers.show'=> [
                'label'=> $palletDelivery->name,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'shop'    => $palletDelivery->shop->slug,
                        'customer'=> $palletDelivery->slug
                    ]

                ]
            ], default => []
        };
    }
}
