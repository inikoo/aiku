<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Apr 2024 13:42:37 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Billing;

use App\Actions\RetinaAction;
use App\Http\Resources\Accounting\InvoicesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\Invoice;
use App\Models\CRM\Customer;
use App\Models\CRM\WebUser;
use App\Models\Dropshipping\CustomerClient;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Catalogue\Shop;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Ordering\Order;
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

class IndexRetinaInvoices extends RetinaAction
{
    private Group|Organisation|Fulfilment|Customer|CustomerClient|FulfilmentCustomer|Shop $parent;

    public function handle(Group|Organisation|Fulfilment|Customer|CustomerClient|FulfilmentCustomer|Shop|Order $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereWith('reference', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }


        $queryBuilder = QueryBuilder::for(Invoice::class);

        if ($parent instanceof Organisation) {
            $queryBuilder->where('invoices.organisation_id', $parent->id);
        } elseif ($parent instanceof Shop) {
            $queryBuilder->where('invoices.shop_id', $parent->id);
        } elseif ($parent instanceof Fulfilment) {
            $queryBuilder->where('invoices.shop_id', $parent->shop->id);
        } elseif ($parent instanceof FulfilmentCustomer) {
            $queryBuilder->where('invoices.customer_id', $parent->customer->id);
        } elseif ($parent instanceof Customer) {
            $queryBuilder->where('invoices.customer_id', $parent->id);
        } elseif ($parent instanceof CustomerClient) {
            $queryBuilder->where('invoices.customer_client_id', $parent->id);
        } elseif ($parent instanceof Order) {
            $queryBuilder->where('invoices.order_id', $parent->id);
        } elseif ($parent instanceof Group) {
            $queryBuilder->where('invoices.group_id', $parent->id);
        } else {
            abort(422);
        }

        $queryBuilder->whereNull('paid_at');

        $queryBuilder->leftjoin('organisations', 'invoices.organisation_id', '=', 'organisations.id');
        $queryBuilder->leftjoin('shops', 'invoices.shop_id', '=', 'shops.id');

        $queryBuilder->defaultSort('-invoices.date')
            ->select([
                'invoices.reference',
                'invoices.total_amount',
                'invoices.net_amount',
                'invoices.date',
                'invoices.type',
                'invoices.created_at',
                'invoices.updated_at',
                'invoices.slug',
                'currencies.code as currency_code',
                'currencies.symbol as currency_symbol',
                'shops.name as shop_name',
                'shops.slug as shop_slug',
                'shops.code as shop_code',
                'organisations.name as organisation_name',
                'organisations.slug as organisation_slug',
            ])
            ->leftJoin('currencies', 'invoices.currency_id', 'currencies.id')
            ->leftJoin('invoice_stats', 'invoices.id', 'invoice_stats.invoice_id');



        if ($parent instanceof Shop) {
            $queryBuilder->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id')
                ->addSelect('customers.name as customer_name', 'customers.slug as customer_slug');
        }

        if ($parent instanceof Fulfilment) {
            $queryBuilder->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id')
                ->leftJoin('fulfilment_customers', 'customers.id', '=', 'fulfilment_customers.customer_id')
                ->addSelect('customers.name as customer_name', 'fulfilment_customers.slug as customer_slug');
        }

        return $queryBuilder->allowedSorts(['number', 'total_amount', 'net_amount', 'date', 'customer_name', 'reference'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Group|Organisation|Fulfilment|Customer|CustomerClient|FulfilmentCustomer|Shop|Order $parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }


            $noResults = __("No invoices found");
            if ($parent instanceof Customer) {
                $stats     = $parent->stats;
                $noResults = __("Customer hasn't been invoiced");
            } elseif ($parent instanceof CustomerClient) {
                $stats     = $parent->stats;
                $noResults = __("This customer client hasn't been invoiced");
            } elseif ($parent instanceof Group) {
                $stats     = $parent->orderingStats;
                $noResults = __("This group hasn't been invoiced");
            } else {
                $stats = $parent->salesStats;
            }

            $table
                ->withGlobalSearch()
                ->withEmptyState(
                    [
                        'title' => $noResults,
                        'count' => $stats->number_invoices ?? 0,
                    ]
                );

            $table->column(key: 'type', label: '', canBeHidden: false, searchable: true, type: 'icon')
                ->defaultSort('reference');
            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true, align: 'right');


            if ($parent instanceof Fulfilment || $parent instanceof Shop) {
                $table->column(key: 'customer_name', label: __('customer'), canBeHidden: false, sortable: true, searchable: true);
            }
            if ($parent instanceof Group) {
                $table->column(key: 'organisation_name', label: __('organisation'), canBeHidden: false, searchable: true);
                $table->column(key: 'shop_name', label: __('shop'), canBeHidden: false, searchable: true);
            }


            $table->column(key: 'total_amount', label: __('total'), canBeHidden: false, sortable: true, searchable: true, type: 'number')
                ->defaultSort('reference');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($request->user() instanceof  WebUser) {
            return true;
        }

        if ($this->parent instanceof Organisation) {
            return $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.view");
        } elseif ($this->parent instanceof Customer or $this->parent instanceof CustomerClient) {
            return $request->user()->hasPermissionTo("crm.{$this->organisation->id}.view");
        } elseif ($this->parent instanceof Shop) {
            //todo think about it
            $permission = $request->user()->hasPermissionTo("orders.{$this->shop->id}.view");
            return $permission;
        } elseif ($this->parent instanceof FulfilmentCustomer or $this->parent instanceof Fulfilment) {
            return $request->user()->hasPermissionTo("fulfilment-shop.{$this->fulfilment->id}.view");
        } elseif ($this->parent instanceof Group) {
            return $request->user()->hasPermissionTo("group-overview");
        }

        return false;
    }


    public function jsonResponse(LengthAwarePaginator $invoices): AnonymousResourceCollection
    {
        return InvoicesResource::collection($invoices);
    }


    public function htmlResponse(LengthAwarePaginator $invoices, ActionRequest $request): Response
    {
        $title = __('Invoices');

        $icon  = [
            'icon'  => ['fal', 'fa-file-invoice-dollar'],
            'title' => __('invoices')
        ];

        $afterTitle = null;
        $iconRight  = null;
        $model      = null;
        $actions    = null;

        if ($this->parent instanceof FulfilmentCustomer) {
            $icon       = ['fal', 'fa-user'];
            $title      = __('Invoices');
            // $iconRight  = [
            //     'icon' => 'fal fa-file-invoice-dollar',
            // ];
            // $afterTitle = [

            //     'label' => __('invoices')
            // ];
            $icon  = [
                'icon'  => ['fal', 'fa-file-invoice-dollar'],
                'title' => __('customer')
            ];
            $model = __('Billing');
        } elseif ($this->parent instanceof Fulfilment) {
            $model = __('Operations');
        } elseif ($this->parent instanceof CustomerClient) {

            $iconRight  = $icon;
            $afterTitle = [
                'label' => $title
            ];

            $title      = $this->parent->name;
            $model      = __('customer client');
            $icon       = [
                'icon'  => ['fal', 'fa-folder'],
                'title' => __('customer client')
            ];


        } elseif ($this->parent instanceof Customer) {

            $iconRight  = $icon;
            $afterTitle = [
                'label' => $title
            ];
            $title = $this->parent->name;
            $icon       = [
                'icon'  => ['fal', 'fa-user'],
                'title' => __('customer')
            ];


        }

        $routeName       = $request->route()->getName();
        $routeParameters = $request->route()->originalParameters();

        return Inertia::render(
            'Billing/RetinaInvoices',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $routeName,
                    $routeParameters
                ),
                'title'       => __('invoices'),
                'pageHead'    => [

                    'title'         => $title,
                    'model'         => $model,
                    'afterTitle'    => $afterTitle,
                    'iconRight'     => $iconRight,
                    'icon'          => $icon,
                    'actions'       => $actions
                ],
                'data'        => InvoicesResource::collection($invoices),


            ]
        )->table($this->tableStructure($this->parent));
    }

    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $request->user()->customer->fulfilmentCustomer;
        $this->initialisation($request);

        return $this->handle($this->parent);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        return [];
    }
}
