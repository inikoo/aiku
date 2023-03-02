<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 21 Febr 2023 17:54:17 Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Actions\Accounting\ShowAccountingDashboard;
use App\Actions\InertiaAction;
use App\Actions\Marketing\Shop\ShowShop;
use App\Http\Resources\Accounting\PaymentAccountResource;
use App\Http\Resources\Accounting\PaymentResource;
use App\Http\Resources\Accounting\PaymentServiceProviderResource;
use App\Http\Resources\Marketing\DepartmentResource;
use App\Http\Resources\Sales\CustomerResource;
use App\Http\Resources\Sales\InertiaTableCustomerResource;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Central\Tenant;
use App\Models\Marketing\Department;
use App\Models\Marketing\Shop;
use App\Models\Sales\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use ProtoneMedia\LaravelQueryBuilderInertiaJs\InertiaTable;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;


class IndexPayments extends InertiaAction
{
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
            ->select(['payments.reference', 'payments.slug', 'payments.status','payments.date', 'payment_service_providers.slug as payment_service_providers_slug'])
            ->leftJoin('payment_service_providers', 'payment_accounts.payment_service_provider_id', 'payment_service_providers.id')
            ->when($this->parent, function ($query) {
                if (class_basename($this->parent) == 'PaymentServiceProvider') {
                    $query->leftJoin('payment_accounts', 'payments.payment_account_id', 'payment_accounts.id');
                 //   $query->leftJoin('payment_service_providers', 'payment_accounts.payment_service_provider_id', 'payment_service_providers.id')
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
                'title' => __('Payments '),
                'pageHead' => [
                    'title' => __('Payments'),
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


    public function asController(Request $request): LengthAwarePaginator
    {
        $this->fillFromRequest($request);
        $this->parent = tenant();
        $this->routeName = $request->route()->getName();

        return $this->handle();
    }

    public function inPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider): LengthAwarePaginator
    {
        $this->parent = $paymentServiceProvider;
        $this->validateAttributes();

        return $this->handle();
    }

    public function inPaymentAccount(PaymentAccount $paymentAccount): LengthAwarePaginator
    {
        $this->parent = $paymentAccount;
        $this->validateAttributes();

        return $this->handle();
    }

    public function inPaymentAccountInPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider , PaymentAccount $paymentAccount): LengthAwarePaginator
    {
        $this->parent = $paymentAccount;
        $this->validateAttributes();

        return $this->handle();
    }

    public function InShop(Shop $shop): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->validateAttributes();

        return $this->handle();
    }

    public function getBreadcrumbs(): array
    {
        return array_merge(
            (new ShowAccountingDashboard())->getBreadcrumbs(),
            [
                'accounting.payments.index' => [
                    'route' => 'accounting.payments.index',
                    'modelLabel' => [
                        'label' => __('payments')
                    ],
                ],
            ]
        );
    }

}
