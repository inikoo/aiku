<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 15:40:54 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\PaymentAccount\UI;

use App\Actions\InertiaAction;
use App\Http\Resources\Accounting\PaymentAccountResource;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Central\Tenant;
use App\Models\Marketing\Shop;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class IndexPaymentAccounts extends InertiaAction
{
    use HasUIPaymentAccounts;

    private Shop|Tenant|PaymentServiceProvider $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('payment_accounts.code', '~*', "\y$value\y")
                    ->orWhere('payment_accounts.name', '=', $value)
                    ->orWhere('payment_accounts.data', '=', $value);
            });
        });


        return QueryBuilder::for(PaymentAccount::class)
            ->defaultSort('payment_accounts.code')
            ->select(['payment_accounts.code', 'payment_accounts.slug','payment_accounts.name', 'payment_service_providers.slug as payment_service_providers_slug', 'number_payments'])
            ->leftJoin('payment_account_stats', 'payment_accounts.id', 'payment_account_stats.payment_account_id')
            ->leftJoin('payment_service_providers', 'payment_service_provider_id', 'payment_service_providers.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'PaymentServiceProvider') {
                    $query->where('payment_accounts.payment_service_provider_id', $this->parent->id);
                }
            })
            ->allowedSorts(['code', 'name', 'number_payments'])
            ->allowedFilters([$globalSearch])
            ->paginate($this->perPage ?? config('ui.table.records_per_page'))
            ->withQueryString();
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


    public function jsonResponse(): AnonymousResourceCollection
    {
        return PaymentAccountResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $payment_accounts)
    {
        return Inertia::render(
            'Accounting/PaymentAccounts',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title'       => __('Payment Accounts '),
                'pageHead'    => [
                    'title' => __('Payment Accounts'),
                    'create'  => $this->canEdit && $this->routeName=='accounting.payment-accounts.index' ? [
                        'route' => [
                            'name'       => 'accounting.payment-accounts.create',
                            'parameters' => array_values($this->originalParameters)
                        ],
                        'label'=> __('payment account')
                    ] : false,
                ],
                'payment_accounts' => PaymentAccountResource::collection($payment_accounts),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->defaultSort('code');

            $table->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'name', label: __('name'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'payment_service_providers_slug', label: __('slug'), canBeHidden: false, sortable: true, searchable: true);
        });
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        $this->parent    = app('currentTenant');
        $this->initialisation($request);
        return $this->handle();
    }

    public function inPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $paymentServiceProvider;
        $this->initialisation($request);
        return $this->handle();
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->initialisation($request);
        return $this->handle();
    }

}
