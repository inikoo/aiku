<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 23 Jul 2025 08:26:56 British Summer Time, Trnava, Slovakia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\WooCommerce\Product;

use App\Enums\Dropshipping\CustomerSalesChannelStatusEnum;
use App\Models\Catalogue\Product;
use App\Models\Dropshipping\CustomerSalesChannel;
use App\Models\Dropshipping\Portfolio;
use App\Models\Dropshipping\WooCommerceUser;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateWooCustomerSalesChannelPortfolio implements ShouldBeUnique
{
    use AsAction;


    public string $jobQueue = 'woo';

    public function getJobUniqueId(CustomerSalesChannel $customerSalesChannel): string
    {
        return $customerSalesChannel->id;
    }

    public function handle(CustomerSalesChannel $customerSalesChannel, bool $force = false): void
    {
        /** @var WooCommerceUser $wooCommerceUser */
        $wooCommerceUser = $customerSalesChannel->user;

        if (!$wooCommerceUser || $customerSalesChannel->status != CustomerSalesChannelStatusEnum::OPEN) {
            return;
        }

        $wooCommerceUser->setTimeout(30);

        if (!$wooCommerceUser->checkConnection()) {
            $customerSalesChannel->update([
                'ban_stock_update_util' => now()->addSeconds(10)
            ]);

            return;
        }

        if ($customerSalesChannel->ban_stock_update_util !== null) {
            $customerSalesChannel->update([
                'ban_stock_update_util' => null
            ]);
        }

        Portfolio::query()
            ->select([
                'id',
                'item_id',
                'item_type',
                'platform_product_id',
                'platform_status',
                'last_stock_value',
                'stock_last_updated_at',
                'stock_last_fail_updated_at',
                DB::raw("CASE WHEN data->'woo_product'->>'type' = 'variation' THEN data->'woo_product'->>'parent_id' END AS woo_variation_parent_id"),
            ])
            ->where('customer_sales_channel_id', $customerSalesChannel->id)
            ->whereNotNull('platform_product_id')
            ->where('item_type', 'Product')
            ->where('platform_status', true)
            ->with('item:id,available_quantity,is_for_sale,exclusive_for_customer_id,state,available_quantity_updated_at')
            ->chunkById(500, function ($portfolioChunk) use ($customerSalesChannel, $wooCommerceUser, $force): void {
                $updatesByVariationParent = [];

                foreach ($portfolioChunk as $portfolio) {
                    if (!$this->checkIfApplicable($portfolio, $customerSalesChannel, $force)) {
                        continue;
                    }

                    /** @var Product $product */
                    $product = $portfolio->item;

                    $updatesByVariationParent[(int) $portfolio->woo_variation_parent_id][] = [
                        'id'             => $portfolio->platform_product_id,
                        'manage_stock'   => true,
                        'stock_quantity' => self::quantityToSend($product, $customerSalesChannel),
                    ];
                }

                foreach ($updatesByVariationParent as $variationParentId => $updates) {
                    foreach (array_chunk($updates, 20) as $updateChunk) {
                        UpdateBatchWooCustomerSalesChannelPortfolio::dispatch($wooCommerceUser, [
                            'update' => $updateChunk,
                        ], $variationParentId ?: null);
                    }
                }
            });
    }

    /**
     * A portfolio matched to a variation keeps the variation id as its platform product id, and
     * the parent it has to be written under comes from the product the store last returned.
     */
    public static function variationParentId(Portfolio $portfolio): ?int
    {
        if (Arr::get($portfolio->data, 'woo_product.type') !== 'variation') {
            return null;
        }

        return (int) Arr::get($portfolio->data, 'woo_product.parent_id') ?: null;
    }

    public static function quantityToSend(Product $product, CustomerSalesChannel $customerSalesChannel): int
    {
        $availableQuantity = $product->available_quantity ?? 0;

        if (!$product->isSellableThroughSalesChannels()) {
            $availableQuantity = 0;
        }

        if ($customerSalesChannel->stock_threshold > 0 && $availableQuantity <= $customerSalesChannel->stock_threshold) {
            return 0;
        }

        if ($customerSalesChannel->max_quantity_advertise > 0) {
            $availableQuantity = min($availableQuantity, $customerSalesChannel->max_quantity_advertise);
        }

        return (int) $availableQuantity;
    }

    public function checkIfApplicable(Portfolio $portfolio, CustomerSalesChannel $customerSalesChannel, bool $force = false): bool
    {
        $product = $portfolio->item;

        if (!$product instanceof Product) {
            return false;
        }

        if ($force) {
            return true;
        }

        $lastSuccessAt = $portfolio->stock_last_updated_at;
        $lastFailAt = $portfolio->stock_last_fail_updated_at;

        if ($lastFailAt && (!$lastSuccessAt || $lastFailAt->gt($lastSuccessAt))) {
            return $lastFailAt->lt(now()->subDay())
                || ($product->available_quantity_updated_at && $product->available_quantity_updated_at->gt($lastFailAt));
        }

        if (!$lastSuccessAt || $portfolio->last_stock_value === null) {
            return true;
        }

        return (int) $portfolio->last_stock_value !== self::quantityToSend($product, $customerSalesChannel);
    }
}
