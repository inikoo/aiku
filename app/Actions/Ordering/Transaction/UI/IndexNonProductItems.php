<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Jun 2023 20:33:12 Malaysia Time, Pantai Lembeng, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Transaction\UI;

use App\Actions\OrgAction;
use App\InertiaTable\InertiaTable;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use App\Services\QueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;

class IndexNonProductItems extends OrgAction
{
    public function handle(Order $order, $prefix = null): LengthAwarePaginator
    {
        $globalSearch = AllowedFilter::callback('global', function ($query, $value) {
            $query->where(function ($query) use ($value) {
                $query->where('assets.code', '~*', "\y$value\y")
                    ->orWhereStartWith('assets.name', $value);
            });
        });

        if ($prefix) {
            InertiaTable::updateQueryBuilderParameters($prefix);
        }

        $query = QueryBuilder::for(Transaction::class);
        $query->where('transactions.order_id', $order->id);
        $query->leftjoin('orders', 'transactions.order_id', '=', 'orders.id');
        $query->leftjoin('currencies', 'orders.currency_id', '=', 'currencies.id');

        $query->whereNotIn('transactions.model_type', ['Product', 'Service']);

        if ($order->model_type == 'Charge') {
            $query->leftJoin('charges', 'transactions.model_id', '=', 'charges.id');
        }

        if ($order->model_type == 'Adjustment') {
            $query->leftJoin('adjustments', 'transactions.model_id', '=', 'adjustments.id');
        }

        if ($order->model_type == 'ShippingZone') {
            $query->leftJoin('shipping_zones', 'transactions.model_id', '=', 'shipping_zones.id');
        }

        $query->defaultSort('transactions.id')
        ->select([
            'transactions.id',
            'transactions.state',
            'transactions.status',
            'transactions.quantity_ordered',
            'transactions.quantity_bonus',
            'transactions.quantity_dispatched',
            'transactions.quantity_fail',
            'transactions.quantity_cancelled',
            'transactions.gross_amount',
            'transactions.net_amount',
            'transactions.created_at',
            'currencies.code as currency_code',
            'orders.id as order_id',
        ]);

        if ($order->model_type == 'Charge') {
            $query->addSelect([
                'charges.name as charge_name',
            ]);
        }

        if ($order->model_type == 'Adjustment') {
            $query->addSelect([
                'adjustments.type as adjustment_type',
            ]);
        }

        if ($order->model_type == 'ShippingZone') {
            $query->addSelect([
                'shipping_zones.name as shipping_zone_name',
            ]);
        }

        return $query->allowedSorts(['net_amount', 'quantity_ordered'])
            ->allowedFilters([$globalSearch])
            ->withPaginator($prefix)
            ->withQueryString();
    }
}
