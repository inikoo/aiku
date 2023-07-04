<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 21 February 2023 17:54:17 Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider\UI;

use App\Actions\InertiaAction;
use App\Actions\UI\Accounting\AccountingDashboard;
use App\Http\Resources\Accounting\PaymentServiceProviderResource;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Market\Shop;
use App\Models\Tenancy\Tenant;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexPaymentServiceProviders extends InertiaAction
{
    /** @noinspection PhpUndefinedMethodInspection */
    public function handle(Tenant|Shop $parent, $prefix=null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('payment_service_providers.code', 'ILIKE', "%$value%")
                    ->orWhere('payment_service_providers.data', 'ILIKE', "%$value%");
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder=QueryBuilder::for(PaymentServiceProvider::class);
        foreach ($this->elementGroups as $key => $elementGroup) {
            $queryBuilder->whereElementGroup(
                prefix: $prefix,
                key: $key,
                allowedElements: array_keys($elementGroup['elements']),
                engine: $elementGroup['engine']
            );
        }

        return $queryBuilder
            ->defaultSort('payment_service_providers.code')
            ->select(['code', 'slug', 'number_accounts', 'number_payments'])
            ->leftJoin('payment_service_provider_stats', 'payment_service_providers.id', 'payment_service_provider_stats.payment_service_provider_id')
            ->when(true, function ($query) use ($parent) {
                if (class_basename($parent) == 'Shop') {
                    $query->leftJoin('payment_service_provider_shop', 'payment_service_providers.id', 'payment_service_provider_shop.payment_service_provider_id');
                    $query->where('payment_service_provider_shop.shop_id', $parent->id);



                }
            })
            ->allowedSorts(['code', 'number_accounts', 'number_payments'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure($prefix = null): Closure
    {
        return function (InertiaTable $table) use ($prefix) {
            if ($prefix) {
                $table
                    ->name($prefix)
                    ->pageName($prefix.'Page');
            }
            $table
                ->withGlobalSearch()
                ->defaultSort('code')
                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_accounts', label: __('accounts'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_payments', label: __('payments'), canBeHidden: false, sortable: true, searchable: true);
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


    public function jsonResponse(LengthAwarePaginator $paymentServiceProviders): AnonymousResourceCollection
    {
        return PaymentServiceProviderResource::collection($paymentServiceProviders);
    }


    public function htmlResponse(LengthAwarePaginator $paymentServiceProviders): Response
    {
        return Inertia::render(
            'Accounting/PaymentServiceProviders',
            [
                'breadcrumbs' => $this->getBreadcrumbs(),
                'title'       => __('Payment Service Providers'),
                'pageHead'    => [
                    'title' => __('Payment Service Providers'),

                ],
                'payment_service_providers' => PaymentServiceProviderResource::collection($paymentServiceProviders),


            ]
        )->table($this->tableStructure());
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle(app('currentTenant'));
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($request);
        return $this->handle($shop);
    }

    public function getBreadcrumbs($suffix=null): array
    {
        return array_merge(
            AccountingDashboard::make()->getBreadcrumbs('accounting.dashboard', []),
            [
                 [
                     'type'   => 'simple',
                     'simple' => [
                         'route' => [
                             'name' => 'accounting.payment-service-providers.index',
                         ],
                         'label' => __('providers'),
                         'icon'  => 'fal fa-bars',
                     ],
                     'suffix'=> $suffix
                ],
            ]
        );
    }
}
