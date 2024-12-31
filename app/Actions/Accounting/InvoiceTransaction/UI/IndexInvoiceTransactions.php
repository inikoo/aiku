<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 19 Apr 2024 13:42:37 Malaysia Time, Kuala Lumpur , Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\InvoiceTransaction\UI;

use App\Actions\OrgAction;
use App\Actions\SysAdmin\Group\UI\ShowOverviewHub;
use App\Http\Resources\Accounting\InvoiceTransactionsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\Invoice;
use App\Models\Accounting\InvoiceTransaction;
use App\Models\SysAdmin\Group;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexInvoiceTransactions extends OrgAction
{
    public function handle(Group|Invoice $parent, $prefix = null): LengthAwarePaginator
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
        $queryBuilder->leftJoin('historic_assets', 'invoice_transactions.historic_asset_id', 'historic_assets.id');
        $queryBuilder->leftJoin('assets', 'invoice_transactions.asset_id', 'assets.id');

        $queryBuilder->select(
            [
                'historic_assets.code',
                'historic_assets.name',
                'assets.slug',
                DB::raw('SUM(invoice_transactions.quantity) as quantity'),
                DB::raw('SUM(invoice_transactions.net_amount) as net_amount'),
            ]
        );

        if ($parent instanceof Group) {
            $queryBuilder
            ->where('invoice_transactions.group_id', $parent->id)
            ->leftJoin('invoices', 'invoice_transactions.invoice_id', 'invoices.id')
            ->leftJoin('currencies', 'invoices.currency_id', 'currencies.id')
            ->addSelect(
                DB::raw("currencies.code AS currency_code")
            );
        } else {
            $queryBuilder
            ->where('invoice_transactions.invoice_id', $parent->id)
            ->addSelect(
                DB::raw("'{$parent->currency->code}' AS currency_code")
            );
        }

        $queryBuilder->groupBy(
            'historic_assets.code',
            'historic_assets.name',
            'assets.slug'
        );

        $queryBuilder->defaultSort('code');

        return $queryBuilder->allowedSorts(['code', 'name', 'quantity', 'net_amount'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(Group|Invoice $parent, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }


            $table
                ->withModelOperations()
                ->withGlobalSearch();

            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'name', label: __('description'), canBeHidden: false, sortable: true, searchable: true);
            $table->column(key: 'quantity', label: __('quantity'), canBeHidden: false, sortable: true, searchable: true, type: 'number');
            $table->column(key: 'net_amount', label: __('net'), canBeHidden: false, sortable: true, searchable: true, type: 'number');
            $table->defaultSort('-invoice_transactions.updated_at');
        };
    }

    public function htmlResponse(LengthAwarePaginator $transactions, ActionRequest $request): Response
    {
        $title      = __('Transactions');
        $icon       = [
            'icon'  => ['fal', 'fa-exchange-alt'],
            'title' => __('Transactions')
        ];

        return Inertia::render(
            'Org/Accounting/InvoiceTransactions',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Transactions'),
                'pageHead'    => [
                    'title'      => $title,
                    'icon'       => $icon,
                ],

                'data' => InvoiceTransactionsResource::collection($transactions),

            ]
        )->table($this->tableStructure($this->group));
    }


    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = group();
        $this->sales = false;
        $this->initialisationFromGroup($this->parent, $request);

        return $this->handle($this->parent);
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => $routeParameters,
                        'label' => __('Transactions'),
                        'icon'  => 'fal fa-bars'
                    ],
                ],
            ];
        };


        return match ($routeName) {
            'grp.overview.ordering.transactions.index' =>
            array_merge(
                ShowOverviewHub::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name'       => $routeName,
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }

}
