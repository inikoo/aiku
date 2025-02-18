<?php
/*
 * author Arya Permana - Kirin
 * created on 17-02-2025-16h-50m
 * github: https://github.com/KirinZero0
 * copyright 2025
*/

namespace App\Actions\Accounting\PaymentAccountShop\UI;

use App\Actions\Accounting\OrgPaymentServiceProvider\UI\ShowOrgPaymentServiceProvider;
use App\Actions\Accounting\PaymentAccount\UI\ShowPaymentAccount;
use App\Actions\Accounting\PaymentAccount\WithPaymentAccountSubNavigation;
use App\Actions\OrgAction;
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Actions\UI\Accounting\ShowAccountingDashboard;
use App\Http\Resources\Accounting\PaymentAccountShopsResource;
use App\Http\Resources\Accounting\PaymentAccountsResource;
use App\Http\Resources\Catalogue\ShopResource;
use App\InertiaTable\InertiaTable;
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

class IndexPaymentAccountShops extends OrgAction
{
    private PaymentAccount $parent;
    use WithPaymentAccountSubNavigation;

    public function handle(PaymentAccount $paymentAccount, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('payment_accounts.code', $value)
                        ->orWhereStartWith('shops.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(PaymentAccountShop::class);


        $queryBuilder->where('payment_account_shop.payment_account_id', $paymentAccount->id);
        $queryBuilder->leftjoin('shops', 'payment_account_shop.shop_id', 'shops.id');

        return $queryBuilder
            ->defaultSort('payment_account_shop.id')
            ->select([
                'payment_account_shop.id',
                'shops.id as shop_id',
                'shops.code as shop_code',
                'shops.name as shop_name',
                'shops.slug as shop_slug',
            ])
            ->allowedSorts(['shop_code', 'shop_name'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(PaymentAccount $parent, ?array $modelOperations = null, $prefix = null): Closure
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
                        'title'       => __('no shops'),
                        'count'       => $parent->stats->number_pas,
                    ]
                )
                ->column(key: 'shop_name', label: __('name'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('id');
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        if ($this->parent instanceof Group) {
            return $request->user()->authTo("group-overview");
        }
        $this->canEdit = $request->user()->authTo("accounting.{$this->organisation->id}.edit");

        return $request->user()->authTo("accounting.{$this->organisation->id}.view");
    }

    public function jsonResponse(LengthAwarePaginator $paymentAccounts): AnonymousResourceCollection
    {
        return PaymentAccountsResource::collection($paymentAccounts);
    }


    public function htmlResponse(LengthAwarePaginator $paymentAccountShops, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Accounting/PaymentAccountShops',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $request->route()->getName(),
                    $request->route()->originalParameters()
                ),
                'title'       => __('Payment Account Shops'),
                'pageHead'    => [
                    'subNavigation' => $this->getPaymentAccountNavigation($this->parent),
                    'icon'      => ['fal', 'fa-store-alt'],
                    'title'     => __('Payment Account Shops'),
                ],
                'data'             => PaymentAccountShopsResource::collection($paymentAccountShops)


            ]
        )->table($this->tableStructure($this->parent));
    }

    public function asController(Organisation $organisation, PaymentAccount $paymentAccount, ActionRequest $request)
    {
        $this->parent = $paymentAccount;
        $this->initialisation($organisation, $request);

        return $this->handle($paymentAccount);
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
                        'label' => __('Shops'),
                        'icon'  => 'fal fa-bars',

                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.org.accounting.payment-accounts.show.shops.index' =>
            array_merge(
                (new ShowPaymentAccount())->getBreadcrumbs('grp.org.accounting.payment-accounts.show', $routeParameters),
                $headCrumb()
            ),
            default => []
        };
    }
}
