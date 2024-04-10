<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 21 February 2023 17:54:17 Malaga, Spain
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Accounting\OrgPaymentServiceProvider\UI;

use App\Actions\OrgAction;
use App\Actions\UI\Accounting\ShowAccountingDashboard;
use App\Http\Resources\Accounting\SelectOrgPaymentServiceProvidersResource;
use App\InertiaTable\InertiaTable;
use App\Models\Accounting\PaymentServiceProvider;
use App\Models\Market\Shop;
use App\Models\SysAdmin\Organisation;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;
use App\Services\QueryBuilder;

class SelectOrgPaymentServiceProviders extends OrgAction
{
    public function handle(Organisation $parent, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->whereStartWith('payment_service_providers.code', $value)
                    ->orWhereAnyWordStartWith('payment_service_providers.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(PaymentServiceProvider::class);

        $queryBuilder->where('payment_service_providers.group_id', $parent->group_id);


        /*

        if($parent instanceof Organisation) {
            $queryBuilder->where('organisation_id', $parent->id);
        } else {
            $queryBuilder->leftJoin('payment_service_provider_shop', 'payment_service_providers.id', 'payment_service_provider_shop.payment_service_provider_id');
            $queryBuilder->where('payment_service_provider_shop.shop_id', $parent->id);
        }

*/

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

        return $queryBuilder
            ->defaultSort('payment_service_providers.code')
            ->select(['org_payment_service_providers.slug','payment_service_providers.code', 'org_payment_service_providers.code as org_code', 'org_payment_service_providers.slug as org_slug', 'org_payment_service_provider_stats.number_payment_accounts', 'org_payment_service_provider_stats.number_payments','name'])
            ->leftJoin(
                'org_payment_service_providers',
                function ($leftJoin) use ($parent) {
                    $leftJoin->on('payment_service_providers.id', '=', 'org_payment_service_providers.payment_service_provider_id')

                        ->where('org_payment_service_providers.organisation_id', '=', $parent->id)
                     ->leftJoin('org_payment_service_provider_stats', 'org_payment_service_providers.id', 'org_payment_service_provider_stats.org_payment_service_provider_id');


                }
            )


            //'payment_service_providers.id', 'org_payment_service_providers.payment_service_provider_id'

            ->allowedSorts(['code', 'number_payment_accounts', 'number_payments','name'])
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
                ->column(key: 'adoption', label: '')

                ->column(key: 'code', label: __('code'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'name', label: __('Name'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_payment_accounts', label: __('accounts'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'number_payments', label: __('payments'), canBeHidden: false, sortable: true, searchable: true);
        };
    }

    public function authorize(ActionRequest $request): bool
    {
        $this->canEdit = $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.edit");
        return $request->user()->hasPermissionTo("accounting.{$this->organisation->id}.view");
    }





    public function htmlResponse(LengthAwarePaginator $paymentServiceProviders, ActionRequest $request): Response
    {
        return Inertia::render(
            'Org/Accounting/SelectPaymentServiceProviders',
            [
                'breadcrumbs' => $this->getBreadcrumbs($request->route()->originalParameters()),
                'title'       => __('Payment Service Providers'),
                'pageHead'    => [
                    'title' => __('Payment Service Providers'),

                ],
                'data'        => SelectOrgPaymentServiceProvidersResource::collection($paymentServiceProviders),


            ]
        )->table($this->tableStructure());
    }


    public function asController(Organisation $organisation, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisation($organisation, $request);
        return $this->handle($organisation);
    }


    public function getBreadcrumbs(array $routeParameters, $suffix = null): array
    {
        return array_merge(
            ShowAccountingDashboard::make()->getBreadcrumbs('grp.org.accounting.dashboard', $routeParameters),
            [
                [
                    'type'   => 'simple',
                    'simple' => [
                        'route' => [
                            'name'       => 'grp.org.accounting.org-payment-service-providers.index',
                            'parameters' => $routeParameters
                        ],
                        'label' => __('providers'),
                        'icon'  => 'fal fa-bars',
                    ],
                    'suffix' => $suffix
                ],
            ]
        );
    }
}
