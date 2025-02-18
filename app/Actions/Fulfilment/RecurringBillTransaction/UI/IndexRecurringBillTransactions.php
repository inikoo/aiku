<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Jun 2024 22:48:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBillTransaction\UI;

use App\Actions\OrgAction;
use App\Http\Resources\Fulfilment\RecurringBillTransactionsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\RecurringBillTransaction;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use App\Enums\Fulfilment\RecurringBill\RecurringBillStatusEnum;

class IndexRecurringBillTransactions extends OrgAction
{
    public function handle(RecurringBill $recurringBill, $prefix = null): LengthAwarePaginator
    {
        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $queryBuilder = QueryBuilder::for(RecurringBillTransaction::class);
        $queryBuilder->where('recurring_bill_transactions.recurring_bill_id', $recurringBill->id);
        $queryBuilder->join('assets', 'recurring_bill_transactions.asset_id', '=', 'assets.id');


        $queryBuilder->join('historic_assets', 'recurring_bill_transactions.historic_asset_id', '=', 'historic_assets.id');
        $queryBuilder->join('currencies', 'assets.currency_id', '=', 'currencies.id');
        $queryBuilder->leftjoin('rental_agreement_clauses', 'recurring_bill_transactions.rental_agreement_clause_id', '=', 'rental_agreement_clauses.id');
        $queryBuilder->leftjoin('fulfilment_customers', 'recurring_bill_transactions.fulfilment_customer_id', '=', 'fulfilment_customers.id');
        $queryBuilder->leftjoin('fulfilments', 'recurring_bill_transactions.fulfilment_id', '=', 'fulfilments.id');



        // $queryBuilder->leftjoin('organisations', 'recurring_bill_transactions.organisation_id', '=', 'organisations.id');

        $queryBuilder
            ->defaultSort('recurring_bill_transactions.id')
            ->select([
                'recurring_bill_transactions.id',
                'recurring_bill_transactions.recurring_bill_id',
                'recurring_bill_transactions.asset_id',
                'recurring_bill_transactions.net_amount',
                'recurring_bill_transactions.gross_amount',
                'recurring_bill_transactions.item_type',
                'recurring_bill_transactions.item_id',
                'recurring_bill_transactions.start_date',
                'recurring_bill_transactions.end_date',

                'recurring_bill_transactions.pallet_delivery_id',
                'recurring_bill_transactions.pallet_return_id',


                'assets.type as asset_type',
                'recurring_bill_transactions.historic_asset_id',
                'assets.slug as asset_slug',
                'historic_assets.code as asset_code',
                'historic_assets.name as asset_name',
                'historic_assets.price as asset_price',
                'historic_assets.unit as asset_unit',
                'historic_assets.units as asset_units',

                'fulfilment_customers.slug as fulfilment_customer_slug',
                'fulfilments.slug as fulfilment_slug',
              //  'organisations.slug as organisation_slug',

                'recurring_bill_transactions.temporal_quantity',
                'recurring_bill_transactions.quantity',
                'currencies.code as currency_code',
                'rental_agreement_clauses.percentage_off as discount'
            ]);


        return $queryBuilder->allowedSorts(['id', 'asset_code'])
            ->withPaginator($prefix, tableName: request()->route()->getName())
            ->withQueryString();
    }

    public function tableStructure(RecurringBill $recurringBill, ?array $modelOperations = null, $prefix = null, $canEdit = false): Closure
    {
        return function (InertiaTable $table) use ($recurringBill, $modelOperations, $prefix, $canEdit) {
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
                        'icons' => ['fal fa-concierge-bell'],
                        'title' => 'you have no transactions',
                        'count' => $recurringBill->stats->number_transactions,
                    ]
                );

            $table
                ->column(key: 'type', label: __('type'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'description', label: __('description'))
                ->column(key: 'asset_code', label: __('billable'), canBeHidden: false, sortable: true, searchable: true)
             //   ->column(key: 'asset_name', label: __('rental name'))
                ->column(key: 'asset_price', label: __('base price'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'quantity', label: __('quantity'), align: 'right')
                ->column(key: 'total', label: __('net'), canBeHidden: false, sortable: true, searchable: true, className: 'text-right font-mono')
                ->defaultSort('id');
            if ($recurringBill->status == RecurringBillStatusEnum::CURRENT) {
                $table->column(key: 'actions', label: __('action'), canBeHidden: false, sortable: true, searchable: true);
            }

        };
    }


    public function jsonResponse(LengthAwarePaginator $recurringBillTransactions): AnonymousResourceCollection
    {
        return RecurringBillTransactionsResource::collection($recurringBillTransactions);
    }
}
