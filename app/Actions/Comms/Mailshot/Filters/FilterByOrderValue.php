<?php

namespace App\Actions\Comms\Mailshot\Filters;

use App\Enums\Ordering\Order\OrderStateEnum;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class FilterByOrderValue
{
    /**
     * Customers with at least one placed order (not a basket, not cancelled) whose net value,
     * in the shop's currency, is within the range. The order counts whether or not it has been
     * invoiced yet, so a customer can match while their invoiced sales are still zero.
     */
    public function apply(Builder $query, array $filters): Builder
    {
        $range = $this->amountRange($filters);

        if ($range === null) {
            return $query;
        }

        return $query->whereExists(fn (Builder $ordersQuery) => $this->matchingOrders($ordersQuery->select(DB::raw(1)), $range));
    }

    /**
     * The largest matching order of each customer, so a listing can show why the customer matched.
     */
    public function largestMatchingOrderNetAmount(array $filters): ?Closure
    {
        $range = $this->amountRange($filters);

        if ($range === null) {
            return null;
        }

        return fn (Builder $ordersQuery) => $this->matchingOrders($ordersQuery->selectRaw('max(orders.net_amount)'), $range);
    }

    /**
     * @return array{min: ?string, max: ?string}|null
     */
    public function amountRange(array $filters): ?array
    {
        $orderValueFilter = Arr::get($filters, 'by_order_value');
        $isOrderValueActive = is_array($orderValueFilter) ? ($orderValueFilter['value'] ?? false) : $orderValueFilter;

        if (!$isOrderValueActive || !is_array($orderValueFilter) || !is_array($orderValueFilter['value'] ?? null)) {
            return null;
        }

        $amountRange = $orderValueFilter['value']['amount_range'] ?? null;

        if (!is_array($amountRange)) {
            return null;
        }

        $min = $amountRange['min'] ?? null;
        $max = $amountRange['max'] ?? null;

        $min = ($min === null || $min === '') ? null : (string) $min;
        $max = ($max === null || $max === '') ? null : (string) $max;

        if ($min === null && $max === null) {
            return null;
        }

        return ['min' => $min, 'max' => $max];
    }

    /**
     * @param  array{min: ?string, max: ?string}  $range
     */
    protected function matchingOrders(Builder $ordersQuery, array $range): Builder
    {
        $ordersQuery->from('orders')
            ->whereColumn('orders.customer_id', 'customers.id')
            ->whereNotIn('orders.state', [OrderStateEnum::CREATING->value, OrderStateEnum::CANCELLED->value]);

        if ($range['min'] !== null) {
            $ordersQuery->where('orders.net_amount', '>=', $range['min']);
        }

        if ($range['max'] !== null) {
            $ordersQuery->where('orders.net_amount', '<=', $range['max']);
        }

        return $ordersQuery;
    }
}
