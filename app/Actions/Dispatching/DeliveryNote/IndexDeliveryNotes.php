<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 23 Feb 2023 16:47:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote;

use App\Actions\OrgAction;
use App\Actions\UI\Dispatch\ShowDispatchHub;
use App\Enums\UI\DeliveryNotes\DeliveryNotesTabsEnum;
use App\Http\Resources\Dispatching\DeliveryNoteResource;
use App\InertiaTable\InertiaTable;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Catalogue\Shop;
use App\Models\Inventory\Warehouse;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;
use Inertia\Response;

class IndexDeliveryNotes extends OrgAction
{
    private Warehouse|Shop|Order $parent;

    public function handle(Warehouse|Shop|Order $parent, $prefix = null): LengthAwarePaginator
    {
        // dd($parent);
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartsWith('delivery_notes.number', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(DeliveryNote::class);


        if ($parent instanceof Warehouse) {
            $query->where('delivery_notes.warehouse_id', $parent->id);
        } else {
            abort(419);
        }


        return $query->defaultSort('delivery_notes.number')
            ->select(['delivery_notes.number', 'delivery_notes.date', 'delivery_notes.state', 'delivery_notes.created_at', 'delivery_notes.updated_at', 'delivery_notes.slug', 'shops.slug as shop_slug'])
            ->leftJoin('delivery_note_stats', 'delivery_notes.id', 'delivery_note_stats.delivery_note_id')
            ->leftJoin('shops', 'delivery_notes.shop_id', 'shops.id')
            ->allowedSorts(['number', 'date'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();


    }


    public function tableStructure($parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table->column(key: 'number', label: __('number'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("dispatching.{$this->warehouse->id}.view");
    }


    public function jsonResponse(LengthAwarePaginator $deliveryNotes): AnonymousResourceCollection
    {
        return DeliveryNoteResource::collection($deliveryNotes);
    }


    public function htmlResponse(LengthAwarePaginator $deliveryNotes, ActionRequest $request): Response
    {
        // dd(DeliveryNoteResource::collection($deliveryNotes));
        return Inertia::render(
            'Org/Dispatching/DeliveryNotes',
            [
                'breadcrumbs'    => $this->getBreadcrumbs(
                    $request->route()->originalParameters(),
                ),
                'title'          => __('delivery notes'),
                'pageHead'       => [
                    'title' => __('delivery notes'),
                ],
                'data'        => DeliveryNoteResource::collection($deliveryNotes),
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => DeliveryNotesTabsEnum::navigation(),
                ],
                DeliveryNotesTabsEnum::DELIVERY_NOTES->value => $this->tab == DeliveryNotesTabsEnum::DELIVERY_NOTES->value ?
                    fn () => DeliveryNoteResource::collection($deliveryNotes)
                    : Inertia::lazy(fn () => DeliveryNoteResource::collection($deliveryNotes)),


            ]
        )->table($this->tableStructure(parent: $this->parent, prefix:DeliveryNotesTabsEnum::DELIVERY_NOTES->value));
    }


    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request);

        return $this->handle($warehouse);
    }

    public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop);
    }

    public function inOrder(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop);
    }

    public function getBreadcrumbs($routeParameters): array
    {
        return array_merge(
            (new ShowDispatchHub())->getBreadcrumbs($routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.warehouses.show.dispatching.delivery-notes',
                            'parameters' => array_merge(
                                [
                                    '_query' => [
                                        'elements[state]' => 'working'
                                    ]
                                ],
                                $routeParameters
                            )
                        ],
                        'label' => __('Delivery notes'),
                        'icon'  => 'fal fa-bars',
                    ],

                ]
            ]
        );
    }
}
