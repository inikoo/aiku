<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 21 Febr 2023 17:54:17 Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\Payment;

use App\Actions\Accounting\PaymentAccount\ShowPaymentAccount;
use App\Actions\Accounting\PaymentServiceProvider\ShowPaymentServiceProvider;
use App\Actions\Accounting\ShowAccountingDashboard;
use App\Actions\InertiaAction;
use App\Http\Resources\Accounting\PaymentResource;
use App\Models\Accounting\Payment;
use App\Models\Accounting\PaymentAccount;
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
                'title'       => __('Payments '),
                'pageHead'    => [
                    'title' => __('Payments'),
                ],
                'payments'    => PaymentResource::collection($payments),


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
        $this->parent    = tenant();
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

    /** @noinspection PhpUnusedParameterInspection */
    public function inPaymentAccountInPaymentServiceProvider(PaymentServiceProvider $paymentServiceProvider, PaymentAccount $paymentAccount): LengthAwarePaginator
    {
        $this->parent = $paymentAccount;
        $this->validateAttributes();

        return $this->handle();
    }

    public function inShop(Shop $shop): LengthAwarePaginator
    {
        $this->parent = $shop;
        $this->validateAttributes();

        return $this->handle();
    }

    public function getBreadcrumbs(string $routeName, Shop|Tenant|PaymentServiceProvider|PaymentAccount $parent): array
    {
        $headCrumb = function (array $routeParameters = []) use ($routeName) {
            return [
                $routeName => [
                    'route' => $routeName,
                    'routeParameters' => $routeParameters,
                    'modelLabel' => [
                        'label' => __('payments')
                    ]
                ],
            ];
        };

        return match ($routeName) {
            'accounting.payments.index' =>
            array_merge(
                (new ShowAccountingDashboard())->getBreadcrumbs(),
                $headCrumb()
            ),
            'accounting.payment-service-providers.show.payments.index' =>
            array_merge(
                (new ShowPaymentServiceProvider())->getBreadcrumbs($parent),
                $headCrumb([$parent->slug])
            ),
            'accounting.payment-service-providers.show.payment-accounts.show.payments.index' =>
            array_merge(
                (new ShowPaymentAccount())->getBreadcrumbs('accounting.payment-service-providers.show.payment-accounts.show',$parent),
                $headCrumb([$parent->paymentServiceProvider->slug,$parent->slug])
            ),

            'accounting.payment-accounts.show.payments.index' =>
            array_merge(
                (new ShowPaymentAccount())->getBreadcrumbs('accounting.payment-accounts.show',$parent),
                $headCrumb([$parent->slug])
            ),

            default => []
        };

    }

}
