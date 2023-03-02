<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 21 February 2023 17:54:17 Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\PaymentServiceProvider;

use App\Actions\Accounting\ShowAccountingDashboard;
use App\Actions\InertiaAction;
use App\Http\Resources\Accounting\PaymentServiceProviderResource;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class IndexPaymentServiceProviders extends InertiaAction
{
    private Shop|Tenant $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {


                $query->where('payment_service_providers.code', '~*', "\y$value\y")
                    ->orWhere('payment_service_providers.data', '=', $value);
            });
        });


        return QueryBuilder::for(PaymentServiceProvider::class)
            ->defaultSort('payment_service_providers.code')
            ->select(['code', 'slug', 'number_accounts', 'number_payments'])
            ->leftJoin('payment_service_provider_stats','payment_service_providers.id','payment_service_provider_stats.payment_service_provider_id')
            ->allowedSorts(['code', 'number_accounts', 'number_payments'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
    }

    public function authorize(ActionRequest $request): bool
    {
        return
            (
                $request->user()->tokenCan('root') or
                $request->user()->hasPermissionTo('accounting.view')
            );
    }


    public function jsonResponse(): AnonymousResourceCollection
    {
        return PaymentServiceProviderResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $payment_service_providers)
    {
        return Inertia::render(
            'Accounting/PaymentServiceProviders',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title' => __('Payment Service Providers'),
                'pageHead' => [
                    'title' => __('Payment Service Providers'),
                ],
                'payment_service_providers' => PaymentServiceProviderResource::collection($payment_service_providers),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->defaultSort('code');

            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'number_accounts', label: __('accounts'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'number_payments', label: __('payments'), canBeHidden: false, sortable: true, searchable: true);
        });
    }


    public function asController(Request $request): LengthAwarePaginator
    {
        $this->fillFromRequest($request);
        $this->parent = tenant();
        $this->routeName = $request->route()->getName();

        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowAccountingDashboard())->getBreadcrumbs(),
            [
                'accounting.payment-service-providers.index' => [
                    'route' => 'accounting.payment-service-providers.index',
                    'modelLabel' => [
                        'label' => __('Providers')
                    ],
                ],
            ]
        );
    }

}
