<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Thu, 16 Mar 2023 08:17:51 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Actions\Accounting\Payment\UI;


use App\Actions\InertiaAction;
use App\Http\Resources\Accounting\PaymentResource;
use App\Models\Accounting\Payment;
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

class IndexPayments extends InertiaAction
{
    use HasUIPayments;

    private Shop|Tenant|PaymentServiceProvider|PaymentAccount $parent;

    public function handle(): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('payments.reference', '~*', "\y$value\y")
                    ->orWhere('payments.status', '=', $value)
                    ->orWhere('payments.date', '=', $value)
                    ->orWhere('payments.data', '=', $value);
            });
        });


        return QueryBuilder::for(Payment::class)
            ->defaultSort('payments.reference')
            ->select(['payments.reference', 'payments.slug', 'payments.status', 'payments.date',
                'payment_accounts.slug as payment_accounts_slug',
                'payment_service_providers.slug as payment_service_providers_slug'
            ])
            ->leftJoin('payment_accounts', 'payments.payment_account_id', 'payment_accounts.id')
            ->leftJoin('payment_service_providers', 'payment_accounts.payment_service_provider_id', 'payment_service_providers.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'PaymentServiceProvider') {
                    $query->where('payment_accounts.payment_service_provider_id', $this->parent->id);
                }
                if (class_basename($this->parent) == 'PaymentAccount') {
                    $query->where('payments.payment_account_id', $this->parent->id);
                }
                if (class_basename($this->parent) == 'Shop') {
                    $query->where('payments.shop_id', $this->parent->id);
                }
            })
            ->allowedSorts(['payments.reference', 'payments.status', 'payments.date'])
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
        return PaymentResource::collection($this->handle());
    }


    public function htmlResponse(LengthAwarePaginator $payments)
    {
        return Inertia::render(
            'Accounting/Payments',
            [
                'breadcrumbs' => $this->getBreadcrumbs($this->routeName, $this->parent),
                'title' => __('payments '),
                'pageHead' => [
                    'title' => __('payments'),
                    'create' => $this->canEdit &&
                    (
                        $this->routeName == 'accounting.payment-accounts.show.payments.index' or
                        $this->routeName == 'accounting.payment-service-providers.show.payment-accounts.show.payments.index'
                    )

                        ? [
                            'route' =>
                                match ($this->routeName) {
                                    'accounting.payment-accounts.show.payments.index' =>
                                    [
                                        'name' => 'accounting.payment-accounts.show.payments.create',
                                        'parameters' => array_values($this->originalParameters)
                                    ],
                                    'accounting.payment-service-providers.show.payment-accounts.show.payments.index' =>
                                    [
                                        'name' => 'accounting.payment-service-providers.show.payment-accounts.show.payments.create',
                                        'parameters' => array_values($this->originalParameters)
                                    ]
                                }


                            ,
                            'label' => __('payments')
                        ] : false,
                ],
                'payments' => PaymentResource::collection($payments),


            ]
        )->table(function (InertiaTable $table) {
            $table
                ->withGlobalSearch()
                ->defaultSort('reference');

            $table->column(key: 'reference', label: __('reference'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'status', label: __('status'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'data', label: __('data'), canBeHidden: false, sortable: true, searchable: true);

            $table->column(key: 'date', label: __('date'), canBeHidden: false, sortable: true, searchable: true);
        });
    }


    public function asController(ActionRequest $request): LengthAwarePaginator
    {
        //$this->fillFromRequest($request);
        $this->parent = app('currentTenant');
        //$this->routeName = $request->route()->getName();
        $this->initialisation($request);
        return $this->handle();
    }

    public function inPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $paymentServiceProvider;
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle();
    }

    public function inPaymentAccount(PaymentAccount $paymentAccount, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $paymentAccount;
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle();
    }

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentAccountInPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, PaymentAccount $paymentAccount, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $paymentAccount;
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle();
    }

    public function inShop(Shop $shop, ActionRequest $request): LengthAwarePaginator
    {
        $this->parent = $shop;
        //$this->validateAttributes();
        $this->initialisation($request);
        return $this->handle();
    }

}
