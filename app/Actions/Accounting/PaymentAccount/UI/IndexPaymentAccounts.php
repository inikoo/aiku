<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 15:40:54 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\PaymentAccount\UI;

use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\InertiaAction;
use App\Actions\UI\Accounting\AccountingDashboard;
use App\Enums\UI\TabsAbbreviationEnum;
use App\Http\Resources\Accounting\PaymentAccountResource;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Marketing\Shop;
use App\Models\Tenancy\Tenant;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use App\InertiaTable\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexPaymentAccounts extends InertiaAction
{
    public function handle(Shop|Tenant|PaymentServiceProvider $parent,$prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('payment_accounts.code', 'ILIKE', "%$value%")
                    ->orWhere('payment_accounts.name', 'ILIKE', "%$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        return QueryBuilder::for(PaymentAccount::class)
            ->defaultSort('payment_accounts.code')
            ->select(['payment_accounts.code', 'payment_accounts.slug', 'payment_accounts.name', 'payment_service_providers.slug as payment_service_providers_slug', 'number_payments'])
            ->leftJoin('payment_account_stats', 'payment_accounts.id', 'payment_account_stats.payment_account_id')
            ->leftJoin('payment_service_providers', 'payment_service_provider_id', 'payment_service_providers.id')
            ->when(true, function ($query) use ($parent) {
                if (class_basename($parent) == 'PaymentServiceProvider') {
                    $query->where('payment_accounts.payment_service_provider_id', $parent->id);
                } elseif (class_basename($parent) == 'Shop') {
                    $query->leftJoin('payment_account_shop', 'payment_account_shop.payment_account_id', 'payment_accounts.id');
                    $query->leftJoin('shops', 'shops.id', 'payment_account_shop.shop_id');
                    $query->addSelect('shops.slug as shop_slug');
                    $query->where('payment_account_shop.shop_id', $parent->id);
                }
            })
            ->allowedSorts(['code', 'name', 'number_payments'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(): Closure
    {
        return function (InertiaTable $table) {
            $table
                ->name(TabsAbbreviationEnum::PAYMENT_ACCOUNTS->value)
                ->pageName(TabsAbbreviationEnum::PAYMENT_ACCOUNTS->value.'Page')
                ->withGlobalSearch()
                ->defaultSort('code');

            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->can('accounting.edit');

        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('accounting.view')
            );
    }


    public function inTenant(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle(app('currentTenant'));
    }

    public function inPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle($paymentServiceProvider);
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle($shop);
    }

    public function jsonResponse(LengthAwarePaginator $paymentAccounts): AnonymousResourceCollection
    {
        return PaymentAccountResource::collection($paymentAccounts);
    }


    public function htmlResponse(LengthAwarePaginator $paymentAccounts, ActionRequest $request): Response
    {
        $routeName       = $request->route()->getName();
        $routeParameters = $request->route()->parameters;

        return Inertia::render(
            'Accounting/PaymentAccounts',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $routeName,
                    $routeParameters
                ),
                'title'       => __('Payment Accounts'),
                'pageHead'    => [
                    'title'     => __('Payment Accounts'),
                    'create'    => $this->canEdit && $this->routeName == 'accounting.payment-accounts.index' ? [
                        'route' => [
                            'name'       => 'accounting.payment-accounts.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label' => __('payment account')
                    ] : false,
                    'container' => match ($routeName) {
                        'shops.show.accounting.payment-accounts.index' => [
                            'icon'    => ['fal', 'fa-store-alt'],
                            'tooltip' => __('Shop'),
                            'label'   => Str::possessive($routeParameters['shop']->name)
                        ],
                        default => null
                    },


                ],
                'data'        => PaymentAccountResource::collection($paymentAccounts),


            ]
        )->table($this->tableStructure());
    }

    public function getBreadcrumbs(string $routeName, array $routeParameters): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => $routeName,
                            'parameters' => $routeParameters
                        ],
                        'label' => __('payment accounts'),
                        'icon'  => 'fal fa-bars',

                    ],

                ],
            ];
        };

        return match ($routeName) {
            'shops.show.accounting.payment-accounts.index' =>
            array_merge(
                AccountingDashboard::make()->getBreadcrumbs('shops.show.accounting.dashboard', $routeParameters),
                $headCrumb($routeParameters)
            ),
            'accounting.payment-accounts.index' =>
            array_merge(
                AccountingDashboard::make()->getBreadcrumbs('accounting.dashboard', []),
                $headCrumb()
            ),
            'accounting.payment-service-providers.show.payment-accounts.index' =>
            array_merge(
                ShowPaymentServiceProvider::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb($routeParameters)
            ),
            default => []
        };
    }
}
