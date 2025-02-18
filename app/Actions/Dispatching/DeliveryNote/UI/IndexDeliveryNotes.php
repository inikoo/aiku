<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 13:48:15 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dispatching\DeliveryNote\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\CRM\Customer\UI\WithCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Actions\UI\Dispatch\ShowDispatchHub;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\UI\DeliveryNotes\DeliveryNotesTabsEnum;
use App\Http\Resources\Dispatching\DeliveryNotesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Inventory\Warehouse;
use App\Models\Ordering\Order;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexDeliveryNotes extends OrgAction
{
    use WithCustomerSubNavigation;

    private Group|Warehouse|Shop|Order|Customer|CustomerClient $parent;
    private string $bucket;

    public function handle(Group|Warehouse|Shop|Order|Customer|CustomerClient $parent, $prefix = null, $bucket = 'all'): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartsWith('delivery_notes.reference', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(DeliveryNote::class);

        if ($this->bucket == 'unassigned') {
            $query->where('delivery_notes.state', DeliveryNoteStateEnum::UNASSIGNED);
        } elseif ($this->bucket == 'queued') {
            $query->where('delivery_notes.state', DeliveryNoteStateEnum::QUEUED);
        } elseif ($this->bucket == 'handling') {
            $query->where('delivery_notes.state', DeliveryNoteStateEnum::HANDLING);
        } elseif ($this->bucket == 'handling_blocked') {
            $query->where('delivery_notes.state', DeliveryNoteStateEnum::HANDLING_BLOCKED);
        } elseif ($this->bucket == 'packed') {
            $query->where('delivery_notes.state', DeliveryNoteStateEnum::PACKED);
        } elseif ($this->bucket == 'finalised') {
            $query->where('delivery_notes.state', DeliveryNoteStateEnum::FINALISED);
        } elseif ($this->bucket == 'dispatched') {
            $query->where('delivery_notes.state', DeliveryNoteStateEnum::DISPATCHED);
        } elseif ($this->bucket == 'cancelled') {
            $query->where('delivery_notes.state', DeliveryNoteStateEnum::CANCELLED);
        } elseif ($this->bucket == 'dispatched_today') {
            $query->where('delivery_notes.state', DeliveryNoteStateEnum::DISPATCHED)
                    ->where('dispatched_at', Carbon::today());
        }


        if ($parent instanceof Warehouse) {
            $query->where('delivery_notes.warehouse_id', $parent->id);
        } elseif ($parent instanceof Group) {
            $query->where('delivery_notes.group_id', $parent->id);
        } elseif ($parent instanceof Order) {
            $query->leftjoin('delivery_note_order', 'delivery_note_order.delivery_note_id', '=', 'delivery_notes.id');
            $query->where('delivery_note_order.order_id', $parent->id);
        } elseif ($parent instanceof Customer) {
            $query->where('delivery_notes.customer_id', $parent->id);
        } elseif ($parent instanceof CustomerClient) {
            $query->where('delivery_notes.customer_client_id', $parent->id);
        } elseif ($parent instanceof Shop) {
            $query->where('delivery_notes.shop_id', $parent->id);
        } else {
            abort(419);
        }

        $query->leftjoin('customers', 'delivery_notes.customer_id', '=', 'customers.id');
        $query->leftjoin('organisations', 'delivery_notes.organisation_id', '=', 'organisations.id');
        $query->leftjoin('shops', 'delivery_notes.shop_id', '=', 'shops.id');

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
                'delivery_notes.weight',
                'shops.slug as shop_slug',
                'customers.slug as customer_slug',
                'customers.name as customer_name',
                'delivery_note_stats.number_items as number_items',
                'shops.name as shop_name',
                'shops.slug as shop_slug',
                'organisations.name as organisation_name',
                'organisations.slug as organisation_slug',
            ])
            ->leftJoin('delivery_note_stats', 'delivery_notes.id', 'delivery_note_stats.delivery_note_id')
            ->allowedSorts(['reference', 'date', 'number_items', 'customer_name', 'type', 'weight'])
            ->allowedFilters([$globalSearch])
            ->withBetweenDates(['date'])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }


    public function tableStructure($parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($parent, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }

            $table->betweenDates(['date']);

            $noResults = __("No delivery notes found");
            if ($parent instanceof Customer) {
                $stats = $parent->stats;
                $noResults = __("Customer has no delivery notes");
            } elseif ($parent instanceof CustomerClient) {
                $stats = $parent->stats;
                $noResults = __("This customer client hasn't place any delivery notes");
            } elseif ($parent instanceof Group) {
                $stats = $parent->orderingStats;
            } else {
                $stats = $parent->salesStats;
            }

            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title' => $noResults,
                        'count' => $stats->number_delivery_notes ?? 0
                    ]
                );


            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true);
            if (!$parent instanceof Customer) {
                $table->column(key: 'customer_name', label: __('customer'), canBeHidden: false, sortable: true, searchable: true);
            }
            if ($parent instanceof Group) {
                $table->column(key: 'organisation_name', label: __('organisation'), canBeHidden: false, searchable: true);
                $table->column(key: 'shop_name', label: __('shop'), canBeHidden: false, searchable: true);
            }
            $table->column(key: 'weight', label: __('weight'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'number_items', label: __('items'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: '', label: __('export'), canBeHidden: false, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Customer || $this->parent instanceof Shop) {
            return $request->user()->authTo("orders.{$this->shop->id}.view");
        } elseif ($this->parent instanceof Group) {
            return $request->user()->authTo("group-overview");
        } else {
            return $request->user()->authTo("dispatching.{$this->warehouse->id}.view");
        }
    }


    public function jsonResponse(LengthAwarePaginator $deliveryNotes): AnonymousResourceCollection
    {
        return DeliveryNotesResource::collection($deliveryNotes);
    }


    public function htmlResponse(LengthAwarePaginator $deliveryNotes, ActionRequest $request): Response
    {
        $navigation = DeliveryNotesTabsEnum::navigation();
        if ($this->parent instanceof Group) {
            unset($navigation[DeliveryNotesTabsEnum::STATS->value]);
        }

        $subNavigation = null;
        if ($this->parent instanceof Customer) {
            if ($this->parent->is_dropshipping) {
                $subNavigation = $this->getCustomerDropshippingSubNavigation($this->parent, $request);
            } else {
                $subNavigation = $this->getCustomerSubNavigation($this->parent, $request);
            }
        }

        $title      = __('Delivery notes');
        $model      = '';
        $icon       = [
            'icon'  => ['fal', 'fa-truck'],
            'title' => __('Delivery notes')
        ];
        $afterTitle = null;
        $iconRight  = null;
        $actions    = null;


        if ($this->parent instanceof Customer) {
            $iconRight  = $icon;
            $afterTitle = [
                'label' => $title
            ];
            $title      = $this->parent->name;
            $icon       = [
                'icon'  => ['fal', 'fa-user'],
                'title' => __('customer')
            ];
        } elseif ($this->parent instanceof Warehouse) {
            $icon      = ['fal', 'fa-arrow-from-left'];
            $iconRight = [
                'icon' => 'fal fa-truck',
            ];
            $model     = __('Goods Out');
        }


        return Inertia::render(
            'Org/Dispatching/DeliveryNotes',
            [
                'breadcrumbs'                                => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters(),
                ),
                'title'                                      => __('delivery notes'),
                'pageHead'                                   => [
                    'title'         => $title,
                    'icon'          => $icon,
                    'model'         => $model,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    'subNavigation' => $subNavigation,
                    'actions'       => $actions
                ],
                'data'                                       => DeliveryNotesResource::collection($deliveryNotes),
                'tabs'                                       => [
                    'current'    => $this->tab,
                    'navigation' => DeliveryNotesTabsEnum::navigation(),
                ],
                DeliveryNotesTabsEnum::DELIVERY_NOTES->value => $this->tab == DeliveryNotesTabsEnum::DELIVERY_NOTES->value ?
                    fn () => DeliveryNotesResource::collection($deliveryNotes)
                    : Inertia::lazy(fn () => DeliveryNotesResource::collection($deliveryNotes)),


            ]
        )->table($this->tableStructure(parent: $this->parent, prefix: DeliveryNotesTabsEnum::DELIVERY_NOTES->value));
    }


    public function asController(Organisation $organisation, Warehouse $warehouse, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $warehouse;
        $this->bucket = 'all';
        $this->initialisationFromWarehouse($warehouse, $request)->withTab(DeliveryNotesTabsEnum::values());

        return $this->handle($warehouse);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->bucket = 'all';
        $this->initialisationFromShop($shop, $request)->withTab(DeliveryNotesTabsEnum::values());
        return $this->handle($shop);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inCustomer(Organisation $organisation, Shop $shop, Customer $customer, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $customer;
        $this->bucket = 'all';
        $this->initialisationFromShop($shop, $request)->withTab(DeliveryNotesTabsEnum::values());

        return $this->handle($customer);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inOrder(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->bucket = 'all';
        $this->initialisationFromShop($shop, $request)->withTab(DeliveryNotesTabsEnum::values());

        return $this->handle($shop);
    }

    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = group();
        $this->bucket = 'all';
        $this->initialisationFromGroup(group(), $request)->withTab(DeliveryNotesTabsEnum::values());

        return $this->handle($this->parent, DeliveryNotesTabsEnum::DELIVERY_NOTES->value);
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
            'grp.org.shops.show.ordering.delivery-notes.index' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.ordering.delivery-notes.index',
                        'parameters' => $routeParameters
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
            'grp.overview.ordering.delivery-notes.index' =>
            array_merge(
                ShowGroupOverviewHub::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => $routeName,
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
