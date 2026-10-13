<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Jul 2026 10:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Search;

use App\Models\Ordering\Order;
use Illuminate\Support\Arr;
use Lorisleiva\Actions\Concerns\AsAction;

class SearchOrders
{
    use AsAction;
    use WithRawSearchResults;

    protected const int MIN_PRODUCT_QUERY_LENGTH = 3;
    protected const int PRODUCT_RESULTS_LIMIT = 10;

    public function handle(string $query, array $options): array
    {
        $ordersQuery = Order::search($query);
        if ($shopId = Arr::get($options, 'shop_id')) {
            $ordersQuery->where('shop_id', $shopId);
        }

        $orders = array_map(static fn (array $document) => [
            'id'    => (int)$document['id'],
            'code'  => $document['reference'] ?? null,
            'name'  => ($document['contact_name'] ?? null) ?: ($document['company_name'] ?? null),
            'state' => $document['state'] ?? null,
        ], $this->rawDocuments($ordersQuery));

        if ($customerId = Arr::get($options, 'customer_id')) {
            $orders = collect($this->customerOrdersHoldingProduct($query, $customerId))->concat($orders)->unique('id')->values()->all();
        }

        return [
            'scope'   => 'orders',
            'results' => [
                'orders' => $orders,
            ],
        ];
    }

    /**
     * The orders of one customer with a line whose product code or name matches, named after that product.
     *
     * @return array<int, array{id: int, code: ?string, name: ?string, state: ?string}>
     */
    protected function customerOrdersHoldingProduct(string $query, int $customerId): array
    {
        if (mb_strlen($query) < self::MIN_PRODUCT_QUERY_LENGTH) {
            return [];
        }

        return Order::query()
            ->join('transactions', 'transactions.order_id', '=', 'orders.id')
            ->join('historic_assets', 'historic_assets.id', '=', 'transactions.historic_asset_id')
            ->where('orders.customer_id', $customerId)
            ->whereNull('transactions.deleted_at')
            ->where(function ($products) use ($query) {
                $products->whereWith('historic_assets.code', $query)
                    ->orWhereWith('historic_assets.name', $query);
            })
            ->groupBy('orders.id', 'orders.reference', 'orders.state', 'orders.date')
            ->orderByDesc('orders.date')
            ->limit(self::PRODUCT_RESULTS_LIMIT)
            ->toBase()
            ->selectRaw("orders.id, orders.reference, orders.state, min(concat_ws(' ', historic_assets.code, historic_assets.name)) as product")
            ->get()
            ->map(static fn ($order) => [
                'id'    => (int)$order->id,
                'code'  => $order->reference,
                'name'  => $order->product,
                'state' => $order->state,
            ])
            ->all();
    }

}
