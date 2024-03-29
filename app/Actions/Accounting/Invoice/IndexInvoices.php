<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Actions\UI\Accounting\ShowAccountingDashboard;
use App\Http\Resources\Accounting\InvoicesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\Invoice;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class IndexInvoices extends OrgAction
{
    private Organisation|Fulfilment|Shop $parent;

    public function handle(Organisation|Fulfilment|Shop $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('invoices.number', $value);
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
        } else {
            abort(422);
        }

        $queryBuilder->defaultSort('-invoices.date')
            ->select([
                'invoices.number',
                'invoices.total',
                'invoices.net',
                'invoices.date',
                'invoices.type',
                'invoices.created_at',
                'invoices.updated_at',
                'invoices.slug',
                'currencies.code as currency_code',
                'currencies.symbol as currency_symbol',
            ])
            ->leftJoin('currencies', 'invoices.currency_id', 'currencies.id')
            ->leftJoin('invoice_stats', 'invoices.id', 'invoice_stats.invoice_id');


        if (!($parent instanceof Fulfilment or $parent instanceof Shop)) {
            $queryBuilder->leftJoin('shops', 'invoices.shop_id', 'shops.id')
                ->addSelect(['shops.slug as shop_slug', 'shops.name as shop_name', 'shops.code as shop_code']);
        }


        if ($parent instanceof Shop) {
            $queryBuilder->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id')
                ->addSelect('customers.name as customer_name', 'customers.slug as customer_slug');
        }

        if ($parent instanceof Fulfilment) {
            $queryBuilder->leftJoin('customers', 'invoices.customer_id', '=', 'customers.id')
                ->leftJoin('fulfilment_customers', 'customers.id', '=', 'fulfilment_customers.customer_id')
                ->addSelect('customers.name as customer_name', 'fulfilment_customers.slug as customer_slug');
        }

        return $queryBuilder->allowedSorts(['number', 'total', 'net', 'date', 'customer_name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|Fulfilment|Shop $parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->column(key: 'number', label: __('number'), canBeHidden: false, sortable: true, searchable: true);

            if ($parent instanceof Fulfilment || $parent instanceof Shop) {
                $table->column(key: 'customer_name', label: __('customer'), canBeHidden: false, sortable: true, searchable: true);
            }


            $table->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'net', label: __('net'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('number');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Organisation) {
            return $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.view");
        } elseif ($this->parent instanceof Shop) {
            //todo think about it
            return false;
        } elseif ($this->parent instanceof Fulfilment) {
            return $request->user()->hasPermissionTo("fulfilments.{$this->organisation->id}.view");
        }

        return false;
    }


    public function jsonResponse(LengthAwarePaginator $invoices): AnonymousResourceCollection
    {
        return InvoicesResource::collection($invoices);
    }


    public function htmlResponse(LengthAwarePaginator $invoices, ActionRequest $request): Response
    {
        $routeName       = $request->route()->getName();
        $routeParameters = $request->route()->originalParameters();

        return Inertia::render(
            'Accounting/Invoices',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $routeName,
                    $routeParameters
                ),
                'title'       => __('invoices'),
                'pageHead'    => [
                    'title'     => __('invoices'),
                    'container' => match ($routeName) {
                        'grp.org.accounting.shops.show.invoices.index' => [
                            'icon'    => ['fal', 'fa-store-alt'],
                            'tooltip' => __('Shop'),
                            'label'   => Str::possessive($routeParameters['shop']->name)
                        ],
                        default => null
                    },
                ],
                'data'        => InvoicesResource::collection($invoices),


            ]
        )->table($this->tableStructure($this->parent));
    }


    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inFulfilment(Organisation $organisation, Fulfilment $fulfilment, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $fulfilment;
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilment);
    }


    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function () use ($routeName, $routeParameters) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => $routeName,
                            'parameters' => $routeParameters
                        ],
                        'label' => __('invoices'),
                        'icon'  => 'fal fa-bars',

                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.accounting.shops.show.invoices.index' =>
            array_merge(
                ShowAccountingDashboard::make()->getBreadcrumbs('grp.org.accounting.shops.show.dashboard', $routeParameters),
                $headCrumb($routeParameters)
            ),
            'grp.org.accounting.invoices.index' =>
            array_merge(
                ShowAccountingDashboard::make()->getBreadcrumbs('grp.org.accounting.dashboard', $routeParameters),
                $headCrumb($routeParameters)
            ),
            'grp.org.fulfilments.show.operations.invoices.index' =>
            array_merge(
                ShowFulfilment::make()->getBreadcrumbs(routeParameters: $routeParameters),
                $headCrumb($routeParameters)
            ),

            default => []
        };
    }
}
