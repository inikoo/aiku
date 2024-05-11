<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:04:31 Central European Summer, Benalmádena, Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\DeliveryNote;

use App\Actions\InertiaAction;
use App\Actions\Ordering\Order\UI\ShowOrder;
use App\Actions\UI\WithInertia;
use App\Enums\UI\Dispatch\DeliveryNoteTabsEnum;
use App\Http\Resources\Delivery\DeliveryNoteResource;
use App\Models\Catalogue\Shop;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Ordering\Order;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDeliveryNote extends InertiaAction
{
    use AsAction;
    use WithInertia;

    public function handle(DeliveryNote $deliveryNote): DeliveryNote
    {
        return $deliveryNote;
    }

    public function authorize(ActionRequest $request): bool
    {
        //
        return $request->user()->hasPermissionTo("shops.products.view");
    }

    public function inOrganisation(DeliveryNote $deliveryNote): DeliveryNote
    {
        return $this->handle($deliveryNote);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Shop $shop, DeliveryNote $deliveryNote, ActionRequest $request): DeliveryNote
    {
        return $this->handle($deliveryNote);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inOrder(Order $order, DeliveryNote $deliveryNote, ActionRequest $request): DeliveryNote
    {
        return $this->handle($deliveryNote);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inOrderInShop(Shop $shop, Order $order, DeliveryNote $deliveryNote, ActionRequest $request): DeliveryNote
    {
        return $this->handle($deliveryNote);
    }

    public function htmlResponse(DeliveryNote $deliveryNote, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Dispatching/DeliveryNote',
            [
                'title'                                 => __('delivery_note'),
                'breadcrumbs'                           => $this->getBreadcrumbs($request->route()->getName(), $request->route()->originalParameters()(), $deliveryNote),
                'navigation'                            => [
                    'previous' => $this->getPrevious($deliveryNote, $request),
                    'next'     => $this->getNext($deliveryNote, $request),
                ],
                'pageHead'      => [
                    'title' => $deliveryNote->number,


                ],
                'delivery_note' => new DeliveryNoteResource($deliveryNote),

                DeliveryNoteTabsEnum::SHOWCASE->value => $this->tab == DeliveryNoteTabsEnum::SHOWCASE->value ?
                    fn () => GetDeliveryNoteShowcase::run($deliveryNote)
                    : Inertia::lazy(fn () => GetDeliveryNoteShowcase::run($deliveryNote)),
            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->hasPermissionTo('hr.edit'));
        $this->set('canViewUsers', $request->user()->hasPermissionTo('users.view'));
    }

    #[Pure] public function jsonResponse(DeliveryNote $deliveryNote): DeliveryNoteResource
    {
        return new DeliveryNoteResource($deliveryNote);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters, DeliveryNote $deliveryNote): array
    {
        $headCrumb = function (array $routeParameters = []) use ($deliveryNote, $routeName) {
            return [
                $routeName => [
                    'route'           => $routeName,
                    'routeParameters' => $routeParameters,
                    'name'            => $deliveryNote->number,
                    'index'           =>
                        match ($routeName) {
                            'shops.show.orders.show.delivery-notes.show', 'orders.show.delivery-notes.show' => null,

                            default=> [
                                'route'           => preg_replace('/(show|edit)$/', 'index', $routeName),
                                'routeParameters' => array_pop($routeParameters),
                                'overlay'         => __('delivery notes list')
                            ],
                        },


                    'modelLabel'      => [
                        'label' => __('delivery note')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'shops.show.orders.show.delivery-notes.show' =>
            array_merge(
                ShowOrder::make()->getBreadcrumbs(
                    'shops.show.orders.show',
                    [
                        'shop' => $routeParameters['shop'],
                        'order'=> $routeParameters['order']
                    ]
                ),
                $headCrumb([$routeParameters['shop']->slug, $routeParameters['order']->slug,$routeParameters['deliveryNote']->slug])
            ),
            'orders.show.delivery-notes.show' =>
            array_merge(
                ShowOrder::make()->getBreadcrumbs(
                    'shops.show',
                    [
                        'order'=> $routeParameters['order']
                    ],
                ),
                $headCrumb([$routeParameters['order']->slug,$routeParameters['deliveryNote']->slug])
            ),
        };
    }

    public function getPrevious(DeliveryNote $deliveryNote, ActionRequest $request): ?array
    {

        $previous = DeliveryNote::where('number', '<', $deliveryNote->number)->when(true, function ($query) use ($deliveryNote, $request) {
            if ($request->route()->getName() == 'shops.show.delivery-notes.show') {
                $query->where('delivery_notes.shop_id', $deliveryNote->shop_id);
            }
        })->orderBy('number', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(DeliveryNote $deliveryNote, ActionRequest $request): ?array
    {
        $next = DeliveryNote::where('number', '>', $deliveryNote->number)->when(true, function ($query) use ($deliveryNote, $request) {
            if ($request->route()->getName() == 'shops.show.delivery-notes.show') {
                $query->where('delivery_notes.shop_id', $deliveryNote->shop_id);
            }
        })->orderBy('number')->first();

        return $this->getNavigation($next, $request->route()->getName());
    }

    private function getNavigation(?DeliveryNote $deliveryNote, string $routeName): ?array
    {
        if(!$deliveryNote) {
            return null;
        }

        return match ($routeName) {
            'delivery-notes.show' ,
            'shops.delivery-notes.show'=> [
                'label'=> $deliveryNote->number,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'deliveryNote'=> $deliveryNote->slug
                    ]

                ]
            ],
            'shops.show.delivery-notes.show'=> [
                'label'=> $deliveryNote->number,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'shop'        => $deliveryNote->shop->slug,
                        'deliveryNote'=> $deliveryNote->slug
                    ]

                ]
            ]
        };
    }
}
