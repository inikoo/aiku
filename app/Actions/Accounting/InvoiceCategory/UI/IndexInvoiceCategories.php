<?php
/*
 * author Arya Permana - Kirin
 * created on 18-02-2025-09h-55m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\InvoiceCategory\UI;

use App\Actions\Accounting\OrgPaymentServiceProvider\UI\ShowOrgPaymentServiceProvider;
use App\Actions\Accounting\PaymentAccount\UI\ShowPaymentAccount;
use App\Actions\Accounting\PaymentAccount\WithPaymentAccountSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Actions\UI\Accounting\ShowAccountingDashboard;
use App\Http\Resources\Accounting\InvoiceCategoriesResource;
use App\Http\Resources\Accounting\PaymentAccountShopsResource;
use App\Http\Resources\Accounting\PaymentAccountsResource;
use App\Http\Resources\Catalogue\ShopResource;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\InvoiceCategory;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentAccountShop;
use App\Models\Catalogue\Shop;
use App\Models\SysAdmin\Group;
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

class IndexInvoiceCategories extends OrgAction
{
    private Group $parent;

    public function handle(Group $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('invoice_categories.name', $value)
                        ->orWhereStartWith('invoice_categories.slug', $value);
            });
        });

        $queryBuilder = QueryBuilder::for(InvoiceCategory::class);

        $queryBuilder->where('invoice_categories.group_id', $parent->id);
        $queryBuilder->leftjoin('currencies', 'invoice_categories.currency_id', 'currencies.id');

        return $queryBuilder
            ->defaultSort('invoice_categories.id')
            ->select([
                'invoice_categories.id',
                'invoice_categories.slug',
                'invoice_categories.name',
                'invoice_categories.state',
                'invoice_categories.type',
            ])
            ->allowedSorts(['name', 'state'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(Group $parent, ?array $modelOperations = null, $prefix = null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix, $parent) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->withEmptyState(
                    [
                        'title'       => __('no invoice categories'),
                        'count'       => 0,
                    ]
                )
                ->column(key: 'state', label: __('state'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('id');
        };
    }

    public function jsonResponse(LengthAwarePaginator $invoiceCategories): AnonymousResourceCollection
    {
        return InvoiceCategoriesResource::collection($invoiceCategories);
    }


    public function htmlResponse(LengthAwarePaginator $invoiceCategories, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Accounting/InvoiceCategories',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Payment Account Shops'),
                'pageHead'    => [
                    'subNavigation' => '',
                    'icon'      => ['fal', 'fa-store-alt'],
                    'title'     => __('Payment Account Shops'),
                ],
                'data'             => InvoiceCategoriesResource::collection($invoiceCategories)
            ]
        )->table($this->tableStructure($this->parent));
    }

    public function asController(Organisation $organisation, ActionRequest $request)
    {
        $this->parent = group();
        $this->initialisation($organisation, $request);

        return $this->handle(group());
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
                        'label' => __('Invoice Categories'),
                        'icon'  => 'fal fa-bars',

                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.accounting.invoice-categories.index' =>
            array_merge(
                ShowAccountingDashboard::make()->getBreadcrumbs('grp.org.accounting.dashboard', $routeParameters),
                $headCrumb()
            ),
            default => []
        };
    }
}
