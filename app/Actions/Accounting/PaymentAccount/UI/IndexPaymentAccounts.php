<?php

/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 15:40:54 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\PaymentAccount\UI;

use App\Actions\Accounting\OrgPaymentServiceProvider\UI\ShowOrgPaymentServiceProvider;
use App\Actions\OrgAction;
use App\Actions\Overview\ShowGroupOverviewHub;
use App\Actions\UI\Accounting\ShowAccountingDashboard;
use App\Http\Resources\Accounting\PaymentAccountsResource;
use App\Http\Resources\Catalogue\ShopResource;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\OrgPaymentServiceProvider;
use App\Models\Accounting\PaymentAccount;
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

class IndexPaymentAccounts extends OrgAction
{
    private Group|Organisation|Shop|OrgPaymentServiceProvider $parent;

    public function handle(Group|Shop|Organisation|OrgPaymentServiceProvider $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('payment_accounts.code', $value)
                    ->orWhereAnyWordStartWith('payment_accounts.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(PaymentAccount::class);

        if ($parent instanceof Organisation) {
            $queryBuilder->where('payment_accounts.organisation_id', $parent->id);
        } elseif ($parent instanceof OrgPaymentServiceProvider) {
            $queryBuilder->where('org_payment_service_provider_id', $parent->id);
        } elseif ($parent instanceof Shop) {
            $queryBuilder->where('payment_account_shop.shop_id', $parent->id);
        } elseif ($parent instanceof Group) {
            $queryBuilder->where('payment_accounts.group_id', $parent->id);
        }

        /*
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine'],
                prefix: $prefix
            );
        }
        */
        $queryBuilder->leftjoin('organisations', 'payment_accounts.organisation_id', 'organisations.id');
        return $queryBuilder
            ->defaultSort('payment_accounts.code')
            ->select([
                'payment_accounts.id as id',
                'payment_accounts.code as code',
                'payment_accounts.name',
                'payment_account_stats.number_payments',
                'payment_account_stats.number_pas',
                'payment_accounts.slug as slug',
                'payment_service_providers.slug as payment_service_provider_slug',
                'payment_service_providers.name as payment_service_provider_name',
                'payment_service_providers.code as payment_service_provider_code',
                'organisations.name as organisation_name',
                'organisations.slug as organisation_slug',
                'payment_account_stats.number_pas_state_active'
            ])
            ->leftJoin('payment_account_shop', 'payment_account_shop.payment_account_id', 'payment_accounts.id')
            ->leftJoin('payment_account_stats', 'payment_accounts.id', 'payment_account_stats.payment_account_id')
            ->leftJoin('payment_service_providers', 'payment_service_provider_id', 'payment_service_providers.id')
            ->allowedSorts(['code', 'name', 'number_payments','payment_service_provider_code','number_pas_state_active'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(Group|Shop|Organisation|OrgPaymentServiceProvider $parent, ?array $modelOperations = null, $prefix = null): Closure
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
                    $parent instanceof OrgPaymentServiceProvider ?

                        [
                            'title'       => __('no payment accounts'),
                            'description' => $this->canEdit ? __('Get started by creating a new payment account.') : null,
                            'count'       => $parent->stats->number_payment_accounts,
                            'action'      => $this->canEdit ? [
                                'type'    => 'button',
                                'style'   => 'create',
                                'tooltip' => __('new payment account'),
                                'label'   => __('payment account'),
                                'route'   => [
                                    'name'       => 'grp.org.accounting.payment-accounts.create',
                                    'parameters' => $parent->organisation->slug
                                ]
                            ] : null
                        ] : null
                )
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);
            if ($parent instanceof Group) {
                $table->column(key: 'organisation_name', label: __('organisation'), canBeHidden: false, searchable: true);
            }

            if (!$parent instanceof OrgPaymentServiceProvider) {
                $table->column(key: 'payment_service_provider_code', label: __('provider'), canBeHidden: false, sortable: true, searchable: true);
            }

            $table->column(key: 'number_pas_state_active', label: __('shops'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'number_payments', label: __('payments'), canBeHidden: false, sortable: true, searchable: true)
                ->defaultSort('code');
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


    public function inOrganisation(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $organisation;
        $this->initialisation($organisation, $request);

        return $this->handle($organisation);
    }

    public function inOrgPaymentServiceProvider(Organisation $organisation, OrgPaymentServiceProvider $orgPaymentServiceProvider, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $orgPaymentServiceProvider;
        $this->initialisation($organisation, $request);

        return $this->handle($orgPaymentServiceProvider);
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inShop(Organisation $organisation, Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisationFromShop($shop, $request);

        return $this->handle($shop);
    }

    public function inGroup(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = group();
        $this->initialisationFromGroup(group(), $request);

        return $this->handle(group());
    }


    public function jsonResponse(LengthAwarePaginator $paymentAccounts): AnonymousResourceCollection
    {
        return PaymentAccountsResource::collection($paymentAccounts);
    }


    public function htmlResponse(LengthAwarePaginator $paymentAccounts, ActionRequest $request): Response
    {
        $routeName       = $request->route()->getName();
        $routeParameters = $request->route()->originalParameters();

        if ($this->parent instanceof Group) {
            $shops = $this->group->shops;
        } else {
            $shops = $this->organisation->shops;
        }

        return Inertia::render(
            'Org/Accounting/PaymentAccounts',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $routeName,
                    $routeParameters
                ),
                'title'       => __('Payment Accounts'),
                'pageHead'    => [
                    'icon'      => ['fal', 'fa-money-check-alt'],
                    'title'     => __('Payment Accounts'),
                    'actions'   => [
                        $this->canEdit ? [
                            'type'  => 'button',
                            'style' => 'create',
                            'label' => __('payment account'),
                            'route' => [
                                'name'       => 'grp.org.accounting.payment-accounts.create',
                                'parameters' => array_values($request->route()->originalParameters())
                            ]
                        ] : false
                    ],
                    'container' => match ($routeName) {
                        'grp.org.accounting.shops.show.payment-accounts.index' => [
                            'icon'    => ['fal', 'fa-store-alt'],
                            'tooltip' => __('Shop'),
                            'label'   => Str::possessive($routeParameters['shop']->name)
                        ],
                        default => null
                    },


                ],
                'shops_list'       => ShopResource::collection($shops),
                'data'             => PaymentAccountsResource::collection($paymentAccounts)


            ]
            // )->table($this->tableStructure(
            //     parent: $this->parent,
            //     modelOperations: [
            //         'createLink' => $this->canEdit ? [
            //             [
            //             'route' => [
            //                 'name'       => 'grp.org.accounting.payment-accounts.create',
            //                 'parameters' => array_values($request->route()->originalParameters())
            //             ],
            //             'label' => __('payment account')
            //         ]
            //         ] : false,
            //     ],
            // ));
        )->table($this->tableStructure($this->parent));
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
                        'label' => __('Payment accounts'),
                        'icon'  => 'fal fa-bars',

                    ],

                ],
            ];
        };

        return match ($routeName) {
            'grp.org.accounting.shops.show.payment-accounts.index' =>
            array_merge(
                ShowAccountingDashboard::make()->getBreadcrumbs('grp.org.accounting.shops.show.dashboard', $routeParameters),
                $headCrumb($routeParameters)
            ),
            'grp.org.accounting.payment-accounts.index' =>
            array_merge(
                ShowAccountingDashboard::make()->getBreadcrumbs('grp.org.accounting.dashboard', $routeParameters),
                $headCrumb($routeParameters)
            ),
            'grp.org.accounting.org-payment-service-providers.show.payment-accounts.index' =>
            array_merge(
                ShowOrgPaymentServiceProvider::make()->getBreadcrumbs(
                    $routeParameters
                ),
                $headCrumb($routeParameters)
            ),
            'grp.overview.accounting.payment-accounts.index' =>
            array_merge(
                ShowGroupOverviewHub::make()->getBreadcrumbs(),
                $headCrumb(
                    [
                        'name' => $routeName,
                        'parameters' => $routeParameters
                    ]
                )
            ),
            default => []
        };
    }
}
