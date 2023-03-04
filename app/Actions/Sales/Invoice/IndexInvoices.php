<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 21 February 2023 17:54:17 Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Sales\Invoice;

use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Http\Resources\Sales\InvoiceResource;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;
use App\Models\Sales\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class IndexInvoices extends InertiaAction
{
    private Shop|Tenant $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {


                $query->where('invoices.number', '~*', "\y$value\y")
                    ->orWhere('invoices.total', '=', $value)
                    ->orWhere('invoices.net', '=', $value);
            });
        });


        return QueryBuilder::for(Invoice::class)
            ->defaultSort('invoices.number')
            ->select(['invoices.number', 'invoices.total','invoices.net', 'invoices.created_at', 'invoices.updated_at', 'invoices.slug', 'shops.slug as shop_slug'])
            ->leftJoin('invoice_stats', 'invoices.id', 'invoice_stats.invoice_id')
            ->leftJoin('shops', 'invoices.shop_id', 'shops.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'Shop') {
                    $query->where('invoices.shop_id', $this->parent->id);
                }
            })
            ->allowedSorts(['number', 'total', 'net'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('shops.products.view')
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return InvoiceResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $invoices)
    {
        return Inertia::render(
            'Marketing/Invoices',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title' => __('invoices'),
                'pageHead' => [
                    'title' => __('invoices'),
                ],
                'invoices' => InvoiceResource::collection($invoices),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->defaultSort('number');

            $table->column(key: 'number', label: __('number'), canBeHidden: false, sortable: true, searchable: true);


            $table->column(key: 'total', label: __('total'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'net', label: __('net'), canBeHidden: false, sortable: true, searchable: true);

        });
    }


    public function asController(Request $request): LengthAwarePaginator
    {
        $this->fillFromRequest($request);
        $this->parent = app('currentTenant');
        $this->routeName = $request->route()->getName();

        return $this->handle();
    }

    public function InShop(Shop $shop): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->validateAttributes();

        return $this->handle();
    }

    public function getBreadcrumbs(string $routeName, Shop|Tenant $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route' => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel' => [
                        'label' => __('invoices')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'invoices.index' => $headCrumb(),
            'shops.show.invoices.index' =>
            array_merge(
                (new ShowShop())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            default => []
        };
    }

}
