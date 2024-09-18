<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:48:15 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote\UI;

use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\CRM\Customer\UI\WithCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\UI\Dispatch\ShowDispatchHub;
use App\Enums\UI\DeliveryNotes\DeliveryNotesTabsEnum;
use App\Http\Resources\Dispatching\DeliveryNoteResource;
use App\Http\Resources\Dispatching\DeliveryNotesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Inventory\Warehouse;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Cassandra\Type\Custom;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexDeliveryNotes extends OrgAction
{
    use WithCustomerSubNavigation;
    private Warehouse|Shop|Order|Customer $parent;

    public function handle(Warehouse|Shop|Order|Customer $parent, $prefix = null): LengthAwarePaginator
    {
        // dd($parent);
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartsWith('delivery_notes.reference', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(DeliveryNote::class);


        if ($parent instanceof Warehouse) {
            $query->where('delivery_notes.warehouse_id', $parent->id);
        } elseif ($parent instanceof Order) {
            $query->leftjoin('delivery_note_order', 'delivery_note_order.delivery_note_id', '=', 'delivery_notes.id');
            $query->where('delivery_note_order.order_id', $parent->id);
        } elseif ($parent instanceof Customer) {
            $query->where('delivery_notes.customer_id', $parent->id);
        } else {
            abort(419);
        }

        $query->leftjoin('customers', 'delivery_notes.customer_id', '=', 'customers.id');

        return $query->defaultSort('delivery_notes.reference')
            ->select([
                'delivery_notes.id',
                'delivery_notes.reference',
                'delivery_notes.date',
                'delivery_notes.state',
                'delivery_notes.created_at',
                'delivery_notes.updated_at',
                'delivery_notes.slug',
                'delivery_notes.type',
                'delivery_notes.state',
                'delivery_notes.status',
                'delivery_notes.weight',
                'shops.slug as shop_slug',
                'customers.slug as customer_slug',
                'customers.name as customer_name',
                'delivery_note_stats.number_items as number_items'
                ])
            ->leftJoin('delivery_note_stats', 'delivery_notes.id', 'delivery_note_stats.delivery_note_id')
            ->leftJoin('shops', 'delivery_notes.shop_id', 'shops.id')
            ->allowedSorts(['reference', 'date', 'number_items', 'customer_name', 'type', 'status', 'weight'])
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

            $table->column(key: 'status', label: __('status'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true);
            if (!$parent instanceof Customer){
                $table->column(key: 'customer_name', label: __('customer'), canBeHidden: false, sortable: true, searchable: true);
            }
            $table->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'weight', label: __('weight'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'number_items', label: __('items'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: '', label: __('export'), canBeHidden: false, sortable: false, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Customer) {
            return $request->user()->hasPermissionTo("orders.{$this->shop->id}.view");
        } else {
            return $request->user()->hasPermissionTo("dispatching.{$this->warehouse->id}.view");
        }
    }


    public function jsonResponse(LengthAwarePaginator $deliveryNotes): AnonymousResourceCollection
    {
        return DeliveryNotesResource::collection($deliveryNotes);
    }


    public function htmlResponse(LengthAwarePaginator $deliveryNotes, ActionRequest $request): Response
    {
        // dd(DeliveryNoteResource::collection($deliveryNotes));
        $subNavigation = null;
        if ($this->parent instanceof Customer) {
            if ($this->parent->is_dropshipping == true) {
                $subNavigation = $this->getCustomerDropshippingSubNavigation($this->parent, $request);
            } else {
                $subNavigation = $this->getCustomerSubNavigation($this->parent, $request);
            }
        }
        return Inertia::render(
            'Org/Dispatching/DeliveryNotes',
            [
                'breadcrumbs'    => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters(),
                ),
                'title'          => __('delivery notes'),
                'pageHead'       => [
                    'title' => __('delivery notes'),
                    'subNavigation' => $subNavigation,
                ],
                'data'        => DeliveryNotesResource::collection($deliveryNotes),
                'tabs'        => [
                    'current'    => $this->tab,
                    'navigation' => DeliveryNotesTabsEnum::navigation(),
                ],
                DeliveryNotesTabsEnum::DELIVERY_NOTES->value => $this->tab == DeliveryNotesTabsEnum::DELIVERY_NOTES->value ?
                    fn () => DeliveryNotesResource::collection($deliveryNotes)
                    : Inertia::lazy(fn () => DeliveryNotesResource::collection($deliveryNotes)),


            ]
        )->table($this->tableStructure(parent: $this->parent, prefix:DeliveryNotesTabsEnum::DELIVERY_NOTES->value));
    }


    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(DeliveryNotesTabsEnum::values());

        return $this->handle($warehouse);
    }

    public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromShop($shop, $request)->withTab(DeliveryNotesTabsEnum::values());

        return $this->handle($shop);
    }

    public function inCustomer(Organisation $organisation, Shop $shop, Customer $customer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $customer;
        $this->initialisationFromShop($shop, $request)->withTab(DeliveryNotesTabsEnum::values());

        return $this->handle($customer);
    }

    public function inOrder(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromShop($shop, $request)->withTab(DeliveryNotesTabsEnum::values());

        return $this->handle($shop);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Delivery notes'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.warehouses.show.dispatching.delivery-notes' =>
            array_merge(
                ShowDispatchHub::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.warehouses.show.dispatching.delivery-notes',
                        'parameters' => array_merge(
                                [
                                    '_query' => [
                                        'elements[state]' => 'working'
                                    ]
                                ],
                                $routeParameters
                            )
                    ]
                )
            ),
            'grp.org.shops.show.crm.customers.show.delivery_notes.index' =>
            array_merge(
                ShowCustomer::make()->getBreadcrumbs('grp.org.shops.show.crm.customers.show', $routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.crm.customers.show.delivery_notes.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
