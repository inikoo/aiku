<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 25 Mar 2023 01:37:38 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Invoice;

use App\Actions\InertiaAction;
use App\Actions\UI\Accounting\AccountingDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Accounting\InvoiceResource;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\Invoice;
use App\Models\Marketing\Shop;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexInvoices extends InertiaAction
{
    public function handle($parent): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('invoices.number', '~*', "\y$value\y")
                    ->orWhere('invoices.total', '=', $value)
                    ->orWhere('invoices.net', '=', $value);
            });
        });

        InertiaTable::updateQueryBuilderParameters(TabsAbbreviationEnum::INVOICES->value);
        return QueryBuilder::for(Invoice::class)
            ->defaultSort('invoices.number')
            ->select([
                'invoices.number',
                'invoices.total',
                'invoices.net',
                'invoices.created_at',
                'invoices.updated_at',
                'invoices.slug',
                'shops.slug as shop_slug'
            ])
            ->leftJoin('invoice_stats', 'invoices.id', 'invoice_stats.invoice_id')
            ->leftJoin('shops', 'invoices.shop_id', 'shops.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->where('invoices.shop_id', $parent->id);
                }
            })
            ->allowedSorts(['number', 'total', 'net'])
            ->allowedFilters([$globalSearch])
            ->paginate(
                perPage: $this->perPage ?? config('ui.table.records_per_page'),
                pageName: TabsAbbreviationEnum::INVOICES->value.'Page'
            )
            ->withQueryString();
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->name(TabsAbbreviationEnum::INVOICES->value)
                ->pageName(TabsAbbreviationEnum::INVOICES->value.'Page');

            $table->column(key: 'number', label: __('number'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'total', label: __('total'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'net', label: __('net'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.products.view')
            );
    }


    public function jsonResponse(LengthAwarePaginator $invoices): AnonymousResourceCollection
    {
        return InvoiceResource::collection($invoices);
    }


    public function htmlResponse(LengthAwarePaginator $invoices, ActionRequest $request): Response
    {
        $routeName       = $request->route()->getName();
        $routeParameters = $request->route()->parameters;

        return Inertia::render(
            'Marketing/Invoices',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $routeName,
                    $routeParameters
                ),
                'title'       => __('invoices'),
                'pageHead'    => [
                    'title'     => __('invoices'),
                    'container' => match ($routeName) {
                        'shops.show.accounting.invoices.index' => [
                            'icon'    => ['fal', 'fa-store-alt'],
                            'tooltip' => __('Shop'),
                            'label'   => Str::possessive($routeParameters['shop']->name)
                        ],
                        default => null
                    },
                ],
                'data' => InvoiceResource::collection($invoices),


            ]
        )->table($this->tableStructure());
    }


    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->routeName = $request->route()->getName();
        $this->initialisation($request);
        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($shop);
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
            'shops.show.accounting.invoices.index' =>
            array_merge(
                AccountingDashboard::make()->getBreadcrumbs('shops.show.accounting.dashboard', $routeParameters),
                $headCrumb()
            ),
            'accounting.invoices.index' =>
            array_merge(
                AccountingDashboard::make()->getBreadcrumbs('accounting.dashboard', []),
                $headCrumb()
            ),


            default => []
        };


    }
}
