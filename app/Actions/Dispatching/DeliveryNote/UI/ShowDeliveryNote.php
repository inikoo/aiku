<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:48:15 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote\UI;

use App\Actions\Inventory\Warehouse\UI\ShowWarehouse;
use App\Actions\Ordering\Order\UI\ShowOrder;
use App\Actions\OrgAction;
use App\Actions\UI\WithInertia;
use App\Enums\UI\Dispatch\DeliveryNoteTabsEnum;
use App\Http\Resources\Dispatching\DeliveryNoteResource;
use App\Models\Catalogue\Shop;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Inventory\Warehouse;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\Organisation;
use Illuminate\Support\Arr;
use Inertia\Inertia;
use Inertia\Response;
use JetBrains\PhpStorm\Pure;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;

class ShowDeliveryNote extends OrgAction
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
        return $request->user()->hasPermissionTo("dispatching.{$this->warehouse->id}.view");
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

    public function inWarehouse(Organisation $organisation, Warehouse $warehouse, DeliveryNote $deliveryNote, ActionRequest $request): DeliveryNote
    {
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(DeliveryNoteTabsEnum::values());

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
                'title'                                 => __('delivery note'),
                'breadcrumbs' => $this->getBreadcrumbs(
                    $deliveryNote,
                    $request->route()->getName(),
                    $request->route()->originalParameters(),
                ),
                'navigation'                            => [
                    'previous' => $this->getPrevious($deliveryNote, $request),
                    'next'     => $this->getNext($deliveryNote, $request),
                ],
                'pageHead'      => [
                    'title' => $deliveryNote->reference,


                ],
                'delivery_note' => new DeliveryNoteResource($deliveryNote),

                'alert'     => 'zzzzzzzzzz',
                'notes'     => 'zzzzzzzzz',
                'timelines' => 'zzzzzzz',
                'box_stats' => null,
                'routes'    => [
                    'update'    => [
                        'name'          => 'xxxxxxxxxxxxx',
                        'parameters'    => 'xxxxxxx'
                    ],
                    'products_list'    => [
                        'name'          => 'xxxxxxxxxxxxx',
                        'parameters'    => 'xxxxxxx'
                    ],
                ],

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


    public function getBreadcrumbs(DeliveryNote $deliveryNote, string $routeName, array $routeParameters, string $suffix = ''): array
    {
        $headCrumb = function (DeliveryNote $deliveryNote, array $routeParameters, string $suffix) {
            return [
                [

                    'type'           => 'modelWithIndex',
                    'modelWithIndex' => [
                        'index' => [
                            'route' => $routeParameters['index'],
                            'label' => __('Delivery Note')
                        ],
                        'model' => [
                            'route' => $routeParameters['model'],
                            'label' => $deliveryNote->reference,
                        ],

                    ],
                    'suffix'         => $suffix

                ],
            ];
        };

        return match ($routeName) 
        {
            'grp.org.warehouses.show.dispatching.delivery-notes.show',
            => array_merge(
                ShowWarehouse::make()->getBreadcrumbs(
                    Arr::only($routeParameters, ['organisation', 'warehouse'])
                ),
                $headCrumb(
                    $deliveryNote,
                    [
                        'index' => [
                            'name'       => 'grp.org.warehouses.show.dispatching.delivery-notes',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse'])
                        ],
                        'model' => [
                            'name'       => 'grp.org.warehouses.show.dispatching.delivery-notes.show',
                            'parameters' => Arr::only($routeParameters, ['organisation', 'warehouse', 'deliveryNote'])
                        ]
                    ],
                    $suffix
                ),
            ),
            default => []
        };
    }


    public function getPrevious(DeliveryNote $deliveryNote, ActionRequest $request): ?array
    {

        $previous = DeliveryNote::where('reference', '<', $deliveryNote->reference)->when(true, function ($query) use ($deliveryNote, $request) {
            if ($request->route()->getName() == 'shops.show.delivery-notes.show') {
                $query->where('delivery_notes.shop_id', $deliveryNote->shop_id);
            }
        })->orderBy('reference', 'desc')->first();

        return $this->getNavigation($previous, $request->route()->getName());

    }

    public function getNext(DeliveryNote $deliveryNote, ActionRequest $request): ?array
    {
        $next = DeliveryNote::where('reference', '>', $deliveryNote->reference)->when(true, function ($query) use ($deliveryNote, $request) {
            if ($request->route()->getName() == 'shops.show.delivery-notes.show') {
                $query->where('delivery_notes.shop_id', $deliveryNote->shop_id);
            }
        })->orderBy('reference')->first();

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
                'label'=> $deliveryNote->reference,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'deliveryNote'=> $deliveryNote->slug
                    ]

                ]
            ],
            'shops.show.delivery-notes.show'=> [
                'label'=> $deliveryNote->reference,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'shop'        => $deliveryNote->shop->slug,
                        'deliveryNote'=> $deliveryNote->slug
                    ]

                ]
            ],
            'grp.org.warehouses.show.dispatching.delivery-notes.show'=> [
                'label'=> $deliveryNote->reference,
                'route'=> [
                    'name'      => $routeName,
                    'parameters'=> [
                        'organisation'  => $deliveryNote->organisation->slug,
                        'warehouse'     => $deliveryNote->warehouse->slug,
                        'deliveryNote'  => $deliveryNote->slug
                    ]

                ]
            ]
        };
    }
}
