<?php

namespace App\Actions\Maintenance\Ordering;

use App\Actions\Catalogue\Shop\External\Shopify\StoreOrderFromShopifyExternalShop;
use App\Actions\Catalogue\Shop\Traits\WithShopifyExternalShopApi;
use App\Actions\Ordering\Order\CalculateOrderTotalAmounts;
use App\Actions\Ordering\Order\UpdateOrderShippingEngineAsManual;
use App\Actions\Traits\WithActionUpdate;
use App\Enums\Catalogue\Shop\ShopEngineEnum;
use App\Enums\Catalogue\Shop\ShopTypeEnum;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Shop;
use App\Models\Dispatching\DeliveryNote;
use App\Models\Dropshipping\ShopifyUser;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Nightwatch\Facades\Nightwatch;

class RepairShopifyExternalShopOrders
{
    use WithActionUpdate;
    use WithShopifyExternalShopApi;

    /** An order past these states has an invoice or has left: its amounts are history and are left alone. */
    public const array CLOSED_STATES = [OrderStateEnum::FINALISED, OrderStateEnum::DISPATCHED, OrderStateEnum::CANCELLED];

    /** Once a line is picked its amounts follow the picked quantity, so lines are only re-priced before that. */
    public const array STATES_BEFORE_PICKED = [OrderStateEnum::SUBMITTED, OrderStateEnum::IN_WAREHOUSE, OrderStateEnum::HANDLING, OrderStateEnum::HANDLING_BLOCKED];

    public string $commandSignature = 'repair:shopify_external_shop_orders {shop : Slug of the Shopify external shop} {--order= : Only this order reference} {--dry-run : Show what would change, change nothing}';

    /**
     * Brings an order imported under older rules in line with how an order is imported today: the reference the
     * store shows, the shipping the customer paid, and line amounts without tax on the product's current price.
     * The order is read again from Shopify; nothing is written to the store.
     *
     * @return array{reference: string, status: string, changes: array<int, string>}
     */
    public function handle(Order $order, ShopifyUser $shopifyUser, bool $dryRun = false): array
    {
        $report = ['reference' => $order->reference, 'status' => 'unchanged', 'changes' => []];

        if (in_array($order->state, self::CLOSED_STATES) || $order->invoices()->exists()) {
            return [...$report, 'status' => 'skipped', 'changes' => ['already invoiced, dispatched or cancelled']];
        }

        $shopifyOrder = $this->getShopifyOrder($shopifyUser, $order);

        if ($message = Arr::get($shopifyOrder, 'message')) {
            return [...$report, 'status' => 'failed', 'changes' => [$message]];
        }

        $store   = StoreOrderFromShopifyExternalShop::make();
        $changes = [];

        if ($dryRun) {
            DB::beginTransaction();
        }

        try {
            $changes = [
                ...$this->repairReference($order, $shopifyOrder),
                ...$this->repairLines($order, $shopifyOrder, $store),
                ...$this->repairShipping($order, $shopifyOrder, $store),
                ...$this->repairData($order, $shopifyOrder, $store),
            ];

            if ($changes) {
                CalculateOrderTotalAmounts::run($order->refresh());
            }

            $order->refresh();
            $shopifyTotal = (float) Arr::get($shopifyOrder, 'totalPriceSet.shopMoney.amount');

            if ($changes && abs((float) $order->total_amount - $shopifyTotal) > 0.01) {
                $changes[] = 'total '.$order->total_amount.' still differs from Shopify '.$shopifyTotal;
            }
        } finally {
            if ($dryRun) {
                DB::rollBack();
            }
        }

        return [
            ...$report,
            'status'  => $changes ? ($dryRun ? 'would change' : 'repaired') : 'unchanged',
            'changes' => $changes,
        ];
    }

    /**
     * @return array<string, mixed> the order as Shopify holds it now, or ['message' => string] when it could not be read
     */
    public function getShopifyOrder(ShopifyUser $shopifyUser, Order $order): array
    {
        $orderGid = (string) $order->marketplace_id;

        if (!str_starts_with($orderGid, self::SHOPIFY_ORDER_GID_PREFIX)) {
            return ['message' => 'the order has no Shopify id'];
        }

        $found = $this->getAllShopifyExternalShopOrders($shopifyUser, 'id:'.Str::after($orderGid, self::SHOPIFY_ORDER_GID_PREFIX));

        if ($message = Arr::get($found, 'message')) {
            return ['message' => 'Shopify could not be read: '.Str::limit($message, 160)];
        }

        return collect($found['orders'])->firstWhere('id', $orderGid) ?? ['message' => 'not found in Shopify'];
    }

    /**
     * The number the store shows, unless another order or delivery note already holds it.
     *
     * @return array<int, string>
     */
    private function repairReference(Order $order, array $shopifyOrder): array
    {
        $number = ltrim(trim((string) Arr::get($shopifyOrder, 'name')), '#');

        if ($number === '' || $order->reference === $number) {
            return [];
        }

        $deliveryNoteIds = $order->deliveryNotes()->pluck('delivery_notes.id');

        $isTaken = DeliveryNote::withTrashed()
            ->where('organisation_id', $order->organisation_id)
            ->whereNotIn('id', $deliveryNoteIds)
            ->whereRaw('lower(reference) = lower(?)', [$number])
            ->exists()
            || Order::withTrashed()
                ->where('shop_id', $order->shop_id)
                ->where('id', '!=', $order->id)
                ->whereRaw('lower(reference) = lower(?)', [$number])
                ->exists();

        if ($isTaken) {
            return [];
        }

        $oldReference = $order->reference;

        DeliveryNote::whereIn('id', $deliveryNoteIds)->where('reference', $oldReference)->get()->each->update(['reference' => $number]);
        $order->update(['reference' => $number]);

        return ["reference $oldReference -> $number"];
    }

    /**
     * @return array<int, string>
     */
    private function repairLines(Order $order, array $shopifyOrder, StoreOrderFromShopifyExternalShop $store): array
    {
        if (!in_array($order->state, self::STATES_BEFORE_PICKED)) {
            return [];
        }

        $taxesIncluded = (bool) Arr::get($shopifyOrder, 'taxesIncluded', false);
        $shopifyLines  = collect(Arr::get($shopifyOrder, 'lineItems.nodes', []));
        $changes       = [];

        /** @var Transaction $transaction */
        foreach ($order->transactions()->where('model_type', 'Product')->get() as $transaction) {
            $product     = Product::find($transaction->model_id);
            $shopifyLine = $shopifyLines->firstWhere('id', $transaction->marketplace_id)
                ?? $shopifyLines->first(fn (array $line) => $product && Arr::get($line, 'variant.id') === $product->marketplace_id);

            if (!$shopifyLine || !$product) {
                continue;
            }

            $amounts = $store->getLineAmounts($shopifyLine, (int) $transaction->quantity_ordered, $taxesIncluded);

            $dataToUpdate = array_filter([
                'net_amount'        => abs((float) $transaction->net_amount - $amounts['net_amount']) >= 0.005 ? $amounts['net_amount'] : null,
                'gross_amount'      => abs((float) $transaction->gross_amount - $amounts['gross_amount']) >= 0.005 ? $amounts['gross_amount'] : null,
                'historic_asset_id' => $product->current_historic_asset_id && $transaction->historic_asset_id !== $product->current_historic_asset_id ? $product->current_historic_asset_id : null,
                'marketplace_id'    => $transaction->marketplace_id ? null : Arr::get($shopifyLine, 'id'),
            ], fn ($value) => $value !== null);

            if (!$dataToUpdate) {
                continue;
            }

            if (Arr::has($dataToUpdate, 'net_amount')) {
                $dataToUpdate['submitted_net_amount'] = $amounts['net_amount'];
                $dataToUpdate['org_net_amount']       = $amounts['net_amount'] * $transaction->org_exchange;
                $dataToUpdate['grp_net_amount']       = $amounts['net_amount'] * $transaction->grp_exchange;
            }

            $before = "net {$transaction->net_amount} gross {$transaction->gross_amount}";
            $transaction->update($dataToUpdate);

            $changes[] = "line {$product->code}: $before -> net {$amounts['net_amount']} gross {$amounts['gross_amount']}".(Arr::has($dataToUpdate, 'historic_asset_id') ? ', current product price' : '');
        }

        return $changes;
    }

    /**
     * @return array<int, string>
     */
    private function repairShipping(Order $order, array $shopifyOrder, StoreOrderFromShopifyExternalShop $store): array
    {
        $shippingAmount = $store->getShippingAmount($shopifyOrder);

        if (abs((float) $order->shipping_amount - $shippingAmount) < 0.005) {
            return [];
        }

        $before = $order->shipping_amount;
        UpdateOrderShippingEngineAsManual::run($order, ['shipping_amount' => $shippingAmount]);

        return ["shipping $before -> $shippingAmount"];
    }

    /**
     * @return array<int, string>
     */
    private function repairData(Order $order, array $shopifyOrder, StoreOrderFromShopifyExternalShop $store): array
    {
        $fulfillmentOrderIds = $store->getFulfillmentOrderIds($shopifyOrder);

        if (!$fulfillmentOrderIds || Arr::get($order->data, 'shopify_fulfillment_order_ids') == $fulfillmentOrderIds) {
            return [];
        }

        $data = $order->data ?? [];
        data_set($data, 'shopify_fulfillment_order_ids', $fulfillmentOrderIds);
        $order->updateQuietly(['data' => $data]);

        return ['Shopify fulfilment order ids saved'];
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $shop = Shop::where('type', ShopTypeEnum::EXTERNAL)
            ->where('engine', ShopEngineEnum::SHOPIFY)
            ->where('slug', $command->argument('shop'))
            ->firstOrFail();

        $shopifyUser = $this->getShopifyExternalShopUser($shop);

        if (!$shopifyUser) {
            $command->error("Shop $shop->slug is not connected to Shopify");

            return 1;
        }

        if ($message = Arr::get($this->getShopifyExternalShopStoreData($shopifyUser), 'message')) {
            $command->error('Shopify could not be read, nothing was checked: '.Str::limit($message, 200));

            return 1;
        }

        $dryRun = (bool) $command->option('dry-run');

        $rows = [];

        Order::where('shop_id', $shop->id)
            ->whereNotNull('marketplace_id')
            ->when($command->option('order'), fn ($query, $reference) => $query->where('reference', $reference))
            ->orderBy('id')
            ->chunkById(50, function ($orders) use ($shopifyUser, $dryRun, &$rows) {
                foreach ($orders as $order) {
                    $report = $this->handle($order, $shopifyUser, $dryRun);
                    $rows[] = [$report['reference'], $report['status'], implode("\n", $report['changes'])];
                }
            });

        $command->table(['Order', 'Result', 'Changes'], $rows);

        $counts = collect($rows)->countBy(fn (array $row) => $row[1]);
        $command->info($counts->map(fn (int $count, string $status) => "$status: $count")->implode(', ') ?: 'No Shopify orders in this shop');

        return 0;
    }
}
