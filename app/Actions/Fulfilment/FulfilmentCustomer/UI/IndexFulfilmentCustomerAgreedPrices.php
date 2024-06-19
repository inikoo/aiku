<?php

/*
 * Author: Arya Permana <aryapermana02@gmail.com>
 * Created: Thu, 19 Jun 2024 11:29:40 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */


namespace App\Actions\Fulfilment\FulfilmentCustomer\UI;

use App\Actions\OrgAction;
use App\Http\Resources\Fulfilment\FulfilmentCustomerAgreedPricesResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\RentalAgreementClause;
use App\Models\SysAdmin\Organisation;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Lorisleiva\Actions\ActionRequest;
use Spatie\QueryBuilder\AllowedFilter;

class IndexFulfilmentCustomerAgreedPrices extends OrgAction
{
    public function asController(Organisation $organisation, Fulfilment $fulfilment, FulfilmentCustomer $fulfilmentCustomer, ActionRequest $request): LengthAwarePaginator
    {
        $this->initialisationFromFulfilment($fulfilment, $request);

        return $this->handle($fulfilmentCustomer);
    }

    public function handle(FulfilmentCustomer $fulfilmentCustomer, $prefix = null)
    {
        // $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
        //     $query->where(function ($query) use ($value) {
        //         $query->whereAnyWordStartWith('rental_agreement_clauses.state', $value);
        //     });
        // });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }
        $queryBuilder = QueryBuilder::for(RentalAgreementClause::class);
        $queryBuilder->where('rental_agreement_clauses.fulfilment_customer_id', $fulfilmentCustomer->id);

        return $queryBuilder
            ->defaultSort('asset_code')
            ->select([
                'rental_agreement_clauses.id',
                'rental_agreement_clauses.percentage_off'
            ])
            ->leftJoin('assets', 'rental_agreement_clauses.asset_id', 'assets.id')
            ->addSelect(
            'assets.code as asset_code',
            'assets.name as asset_name',
            'assets.type as asset_type',
            'assets.price as asset_price',
            'assets.units as asset_units',
            'assets.unit as asset_unit'
        )
            ->allowedSorts(['id','asset_code','asset_type'])
            ->withPaginator($prefix)
            ->withQueryString();
    }

    public function tableStructure(?array $modelOperations = null, $prefix = null)
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
                ->withEmptyState(
                    [
                    'title'       => __("No agreements found"),
                    'description' =>  __('Get started by creating a new rental agreement. ✨')
                    ]
                    )
            ->column(key: 'asset_code', label: __('code'), canBeHidden: false, sortable: true, searchable: false)
            ->column(key: 'asset_name', label: __('name'), canBeHidden: false, sortable: false, searchable: false)
            ->column(key: 'asset_type', label: __('type'), canBeHidden: false, sortable: true, searchable: false)
            ->column(key: 'percentage_off', label: __('discount'), canBeHidden: false, sortable: false, searchable: false)
            ->column(key: 'agreed_price', label: __('agreed price'), canBeHidden: false, sortable: false, searchable: false);
        };
    }

    public function htmlResponse(LengthAwarePaginator $clauses, ActionRequest $request)
    {
        return Inertia::render(
            'Org/Fulfilment/AgreedPriceShowcase',
            [
                'title'       => __('Agreed Prices'),
                'pageHead'    => [
                    'title'     => __('agreed prices'),
                    'icon'      => [
                        'icon'  => ['fal', 'fa-folder-tree'],
                        'title' => __('agreed prices')
                    ],
                ],
                'data'        => FulfilmentCustomerAgreedPricesResource::collection($clauses),
            ]
        )->table($this->tableStructure());
    }
}