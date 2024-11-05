<?php
/*
 * author Arya Permana - Kirin
 * created on 04-11-2024-13h-41m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/

namespace App\Actions\Ordering\Purge\UI;

use App\Actions\Catalogue\Shop\UI\ShowShop;
use App\Actions\CRM\Customer\UI\ShowCustomer;
use App\Actions\CRM\Customer\UI\WithCustomerSubNavigation;
use App\Actions\OrgAction;
use App\Actions\UI\Dispatch\ShowDispatchHub;
use App\Enums\UI\DeliveryNotes\DeliveryNotesTabsEnum;
use App\Http\Resources\Dispatching\DeliveryNotesResource;
use App\Http\Resources\Ordering\PurgesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Catalogue\Shop;
use App\Models\CRM\Customer;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Inventory\Warehouse;
use App\Models\Ordering\Order;
use App\Models\Ordering\Purge;
use App\Models\SysAdmin\Group;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexPurges extends OrgAction
{
    private Shop|Organisation|Group $parent;

    public function handle(Shop|Organisation|Group $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartsWith('purges.id', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(Purge::class);


        if ($parent instanceof Shop) {
            $query->where('shop_id', $parent->id);
        } else {
            abort(419);
        }
        $query->leftjoin('purge_stats', 'purge_stats.purge_id', '=', 'purges.id');

        return $query->defaultSort('purges.id')
            ->select([
                'purges.id',
                'purges.state',
                'purges.type',
                'purges.scheduled_at',
                'purges.start_at',
                'purges.end_at',
                'purges.cancelled_at',
                'purges.inactive_days',
                'purge_stats.estimated_number_orders',
                'purge_stats.estimated_number_transactions',
                'purge_stats.estimated_amount'
            ])
            ->allowedSorts(['id', 'scheduled_at', 'state', 'type', 'estimated_number_orders', 'estimated_number_transactions', 'estimated_amount'])
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


            $noResults = __("No purges found");

            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title' => $noResults,
                    ]
                );


                $table->column(key: 'scheduled_at', label: __('date'), sortable: true, canBeHidden: false, searchable: true);
                $table->column(key: 'type', label: __('type'), sortable: true, canBeHidden: false, searchable: true);
                $table->column(key: 'state', label: __('state'), sortable: true, canBeHidden: false, searchable: true);
                $table->column(key: 'estimated_number_orders', label: __('orders'), sortable: true, canBeHidden: false, searchable: true);
                $table->column(key: 'estimated_number_transactions', label: __('transactions'), sortable: true, canBeHidden: false, searchable: true);
                $table->column(key: 'estimated_amount', label: __('amount'), sortable: true, canBeHidden: false, searchable: true);
            };
    }

    public function authorize(ActionRequest $request): bool
    {
        return $request->user()->hasPermissionTo("orders.{$this->shop->id}.view");
    }


    public function jsonResponse(LengthAwarePaginator $purges): AnonymousResourceCollection
    {
        return PurgesResource::collection($purges);
    }


    public function htmlResponse(LengthAwarePaginator $purges, ActionRequest $request): Response
    {
        $title      = __('Purges');
        $model      = '';
        $icon       = [
            'icon'  => ['fal', 'fa-trash-alt'],
            'title' => __('Purges')
        ];
        $afterTitle = null;
        $iconRight  = null;
        $actions    = [
            [
                'type'    =>    'button',
                                'style'   => 'create',
                                'tooltip' => __('new purge'),
                                'label'   => __('purge'),
                                'route'   => [
                                    'name'       => 'grp.org.shops.show.ordering.purges.create',
                                    'parameters' => $request->route()->originalParameters()
                                ]
            ]
        ];

        return Inertia::render(
            'Org/Ordering/Purges',
            [
                'breadcrumbs'                                => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters(),
                ),
                'title'                                      => __('purges'),
                'pageHead'                                   => [
                    'title'         => $title,
                    'icon'          => $icon,
                    'model'         => $model,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    'actions'       => $actions
                ],
                'data'              => PurgesResource::collection($purges),
            ]
        )->table($this->tableStructure(parent: $this->parent));
    }


    public function asController(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

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
                        'label' => __('Purges'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.shops.show.ordering.purges.index' =>
            array_merge(
                ShowShop::make()->getBreadcrumbs($routeParameters),
                $headCrumb(
                    [
                        'name'       => 'grp.org.shops.show.ordering.purges.index',
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}