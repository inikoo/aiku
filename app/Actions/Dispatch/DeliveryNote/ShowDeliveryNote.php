<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 12 Oct 2022 17:04:31 Central European Summer, Benalmádena, Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatch\DeliveryNote;

use App\Actions\InertiaAction;
use App\Actions\Sales\Order\ShowOrder;
use App\Actions\UI\WithInertia;
use App\Http\Resources\Delivery\DeliveryNoteResource;
use App\Models\Dispatch\DeliveryNote;
use App\Models\Marketing\Shop;
use App\Models\Sales\Order;
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

    public function inTenant(DeliveryNote $deliveryNote): DeliveryNote
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
            'Marketing/DeliveryNote',
            [
                'title'         => __('delivery_note'),
                'breadcrumbs'   => $this->getBreadcrumbs($request->route()->getName(), $request->route()->parameters(), $deliveryNote),
                'pageHead'      => [
                    'title' => $deliveryNote->number,


                ],
                'delivery_note' => new DeliveryNoteResource($deliveryNote),
            ]
        );
    }

    public function prepareForValidation(ActionRequest $request): void
    {
        $this->fillFromRequest($request);

        $this->set('canEdit', $request->user()->can('hr.edit'));
        $this->set('canViewUsers', $request->user()->can('users.view'));
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
}
