<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 14 Jun 2024 22:48:17 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Retina\Storage\RecurringBill\UI;

use App\Actions\OrgAction;
use App\Enums\Catalogue\Service\ServiceStateEnum;
use App\Enums\Fulfilment\FulfilmentTransaction\FulfilmentTransactionTypeEnum;
use App\Http\Resources\Fulfilment\FulfilmentTransactionResource;
use App\Http\Resources\Fulfilment\RecurringBillTransactionsResource;
use App\InertiaTable\InertiaTable;
use App\Models\Fulfilment\FulfilmentTransaction;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\RecurringBillTransaction;
use App\Services\QueryBuilder;
use Closure;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;

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


        $queryBuilder
            ->defaultSort('recurring_bill_transactions.id')
            ->select([
                'recurring_bill_transactions.id',
                'recurring_bill_transactions.asset_id',
                'recurring_bill_transactions.net_amount',
                'recurring_bill_transactions.gross_amount',
                'recurring_bill_transactions.item_type',
                'assets.type as asset_type',
                'recurring_bill_transactions.historic_asset_id',
                'assets.slug as asset_slug',
                'historic_assets.code as asset_code',
                'historic_assets.name as asset_name',
                'historic_assets.price as asset_price',
                'historic_assets.unit as asset_unit',
                'historic_assets.units as asset_units',

                'recurring_bill_transactions.quantity',
                'currencies.code as currency_code',
            ]);


        return $queryBuilder->allowedSorts(['id', 'asset_name'])
            ->withPaginator($prefix)
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
                ->column(key: 'item_type', label: __('type'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'asset_name', label: __('rental'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'quantity', label: __('quantity'), canBeHidden: false, sortable: true, searchable: true)
                ->column(key: 'net_amount', label: __('net'), canBeHidden: false, sortable: true, searchable: true, className: 'text-right font-mono')
                ->defaultSort('id');
        };
    }


    public function jsonResponse(LengthAwarePaginator $recurringBillTransactions): AnonymousResourceCollection
    {
        return RecurringBillTransactionsResource::collection($recurringBillTransactions);
    }
}
