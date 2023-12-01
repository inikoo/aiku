<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 08:17:51 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\UI;

use App\Actions\Accounting\PaymentAccount\UI\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\UI\ShowPaymentServiceProvider;
use App\Actions\InertiaAction;
use App\Actions\UI\Accounting\AccountingDashboard;
use App\Http\Resources\Accounting\PaymentResource;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Market\Shop;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexPayments extends InertiaAction
{
    /** @noinspection PhpUndefinedMethodInspection */
    public function handle($parent, $prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('payments.reference', '~*', "\y$value\y")
                    ->orWhere('payments.status', '=', $value)
                    ->orWhere('payments.date', '=', $value)
                    ->orWhere('payments.data', '=', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(Payment::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('payments.reference')
            ->select([
                'payments.reference',
                'payments.slug',
                'payments.status',
                'payments.date',
                'payment_accounts.slug as payment_accounts_slug',
                'payment_service_providers.slug as payment_service_providers_slug'
            ])
            ->leftJoin('payment_accounts', 'payments.payment_account_id', 'payment_accounts.id')
            ->leftJoin('payment_service_providers', 'payment_accounts.payment_service_provider_id', 'payment_service_providers.id')
            ->when($parent, function ($query) use ($parent) {
                if (class_basename($parent) == 'PaymentServiceProvider') {
                    $query->where('payment_accounts.payment_service_provider_id', $parent->id);
                } elseif (class_basename($parent) == 'PaymentAccount') {
                    $query->where('payments.payment_account_id', $parent->id);
                } elseif (class_basename($parent) == 'Shop') {
                    $query->where('payments.shop_id', $parent->id);
                } elseif (class_basename($parent) == 'Order') {
                    $query->leftJoin(
                        'paymentables',
                        function ($leftJoin) {
                            $leftJoin->on('paymentables.payment_id', 'payments.id');
                            $leftJoin->on(DB::raw('paymentables.paymentable_type'), DB::raw("'Order'"));
                        }
                    );
                    $query->where('paymentables.paymentable_id', $parent->id);
                }
            })
            ->allowedSorts(['payments.reference', 'payments.status', 'payments.date'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(?array $modelOperations = null, $prefix=null): Closure
    {
        return function (InertiaTable $table) use ($modelOperations, $prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->withModelOperations($modelOperations)
                ->defaultSort('reference')
                ->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'status', label: __('status'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo('accounting.edit');

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

    /** @noinspection PhpUnused */
    public function inPaymentAccount(PaymentAccount $paymentAccount, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle($paymentAccount);
    }


    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentAccountInPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, PaymentAccount $paymentAccount, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle($paymentAccount);
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);

        return $this->handle($shop);
    }

    public function jsonResponse($payments): AnonymousResourceCollection
    {
        return PaymentResource::collection($payments);
    }


    public function htmlResponse(LengthAwarePaginator $payments, ActionRequest $request): Response
    {
        $routeName       = $request->route()->getName();
        $routeParameters = $request->route()->parameters;

        return Inertia::render(
            'Accounting/Payments',
            [
                'breadcrumbs' => $this->getBreadcrumbs(
                    $routeName,
                    $routeParameters
                ),
                'title'       => __('payments '),
                'pageHead'    => [
                    'title'     => __('payments'),
                    'container' => match ($routeName) {
                        'grp.accounting.shops.show.payments.index' => [
                            'icon'    => ['fal', 'fa-store-alt'],
                            'tooltip' => __('Shop'),
                            'label'   => Str::possessive($routeParameters['shop']->name)
                        ],
                        default => null
                    },
                ],
                'data'        => PaymentResource::collection($payments),


            ]
        )->table($this->tableStructure());
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
                        'label' => __('payments'),
                        'icon'  => 'fal fa-bars',

                    ],
                ],
            ];
        };

        return match ($routeName) {
            'grp.accounting.shops.show.payments.index' =>
            array_merge(
                AccountingDashboard::make()->getBreadcrumbs('grp.accounting.shops.show.dashboard', $routeParameters),
                $headCrumb()
            ),
            'grp.accounting.payments.index' =>
            array_merge(
                AccountingDashboard::make()->getBreadcrumbs('grp.accounting.dashboard', []),
                $headCrumb()
            ),
            'grp.accounting.payment-service-providers.show.payments.index' =>
            array_merge(
                (new ShowPaymentServiceProvider())->getBreadcrumbs($routeParameters['paymentServiceProvider']),
                $headCrumb()
            ),
            'grp.accounting.payment-service-providers.show.payment-accounts.show.payments.index' =>
            array_merge(
                (new ShowPaymentAccount())->getBreadcrumbs('grp.accounting.payment-service-providers.show.payment-accounts.show', $routeParameters),
                $headCrumb()
            ),

            'grp.accounting.payment-accounts.show.payments.index' =>
            array_merge(
                (new ShowPaymentAccount())->getBreadcrumbs('grp.accounting.payment-accounts.show', $routeParameters),
                $headCrumb()
            ),

            default => []
        };
    }
}
