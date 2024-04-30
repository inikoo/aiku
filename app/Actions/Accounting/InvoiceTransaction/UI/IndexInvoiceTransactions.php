<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Apr 2024 13:42:37 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\InvoiceTransaction\UI;

use App\Actions\Fulfilment\Fulfilment\UI\ShowFulfilment;
use App\Actions\OrgAction;
use App\Actions\UI\Accounting\ShowAccountingDashboard;
use App\Http\Resources\Accounting\InvoicesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexInvoiceTransactions extends OrgAction
{
    private Organisation|Invoice|Shop $parent;

    public function handle(Organisation|Invoice|Shop $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('invoice_transactions.number', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }


        $queryBuilder = QueryBuilder::for(InvoiceTransaction::class);

        if ($parent instanceof Organisation) {
            $queryBuilder->where('invoice_transactions.organisation_id', $parent->id);
        } elseif ($parent instanceof Shop) {
            $queryBuilder->where('invoice_transactions.shop_id', $parent->id);
        } elseif ($parent instanceof Invoice) {
            $queryBuilder->where('invoice_transactions.invoice_id', $parent->id);
        } else {
            abort(422);
        }

        $queryBuilder->defaultSort('-invoice_transactions.updated_at')
            ->leftJoin('products', 'invoice_transactions.product_id', 'products.id');


        return $queryBuilder->allowedSorts(['code'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Organisation|Invoice|Shop $parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'description', label: __('description'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'price', label: __('price'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'quantity', label: __('quantity'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Organisation) {
            return $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.view");
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
            'Org/Accounting/Invoices',
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
