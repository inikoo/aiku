<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\WooCommerce\Product;

use App\Actions\Traits\AsUniqueUntilProcessingAction;
use App\Models\Dropshipping\Portfolio;
use App\Models\Dropshipping\WooCommerceUser;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Support\Facades\Redis;

/**
 * A store's pending product creates wait in a Redis list instead of as queued jobs: a create job
 * is dispatched only for a free slot of the store's funnel, and every create that finishes
 * dispatches the next. Waiting uploads cost nothing, where a job per upload releasing itself
 * until a slot freed was popped hundreds of times for each product it created.
 */
class DispatchWooUploadQueue implements ShouldBeUniqueUntilProcessing
{
    use AsUniqueUntilProcessingAction;

    public string $jobQueue = 'woo';

    /**
     * While a store has uploads waiting it is also looked at on this beat, so a create job the
     * queue lost without finishing cannot leave the rest of the upload waiting for ever.
     */
    public const int CHECK_AGAIN_SECONDS = 60;

    public function getJobUniqueId(WooCommerceUser $wooCommerceUser): int
    {
        return $wooCommerceUser->id;
    }

    /**
     * A dispatched create holds its slot until a worker starts it, or a store whose creates wait in
     * a busy queue looks free and the list drains into jobs that wait for a slot: a slow store took
     * 620 of them past their retry window (customer sales channel 76494, 22 Oct 2026).
     */
    public const int RESERVED_SLOT_SECONDS = 600;

    public static function push(WooCommerceUser $wooCommerceUser, Portfolio $portfolio, bool $checkConnection = true, ?array $bulkProgress = null): void
    {
        $queue = self::queueKey($wooCommerceUser);
        $entry = json_encode([
            'portfolio_id'     => $portfolio->id,
            'check_connection' => $checkConnection,
            'bulk_progress'    => $bulkProgress,
        ]);

        Redis::rpush($queue, $entry);
        self::keepQueue($wooCommerceUser);
    }

    /**
     * Waiting in the list never runs out: a slow store finishes the upload slowly, and each create
     * gets its retry window when it is dispatched.
     */
    public function handle(WooCommerceUser $wooCommerceUser): void
    {
        $queue     = self::queueKey($wooCommerceUser);
        $freeSlots = self::freeSlots($wooCommerceUser);

        while ($freeSlots > 0 && $entry = Redis::lpop($queue)) {
            $entry     = json_decode($entry, true);
            $portfolio = Portfolio::find($entry['portfolio_id']);

            if (!$portfolio || $portfolio->platform_product_id) {
                continue;
            }

            self::reserveSlot($wooCommerceUser, $portfolio);
            StoreNewProductToCurrentWooCommerce::dispatch($wooCommerceUser, $portfolio, $entry['check_connection'], $entry['bulk_progress']);
            $freeSlots--;
        }

        if (Redis::llen($queue)) {
            self::keepQueue($wooCommerceUser);
            self::checkLater($wooCommerceUser);
        }
    }

    public static function checkLater(WooCommerceUser $wooCommerceUser): void
    {
        self::dispatch($wooCommerceUser)->delay(now()->addSeconds(self::CHECK_AGAIN_SECONDS));
    }

    public static function queueKey(WooCommerceUser $wooCommerceUser): string
    {
        return 'woo_product_create_queue_'.$wooCommerceUser->id;
    }

    public static function reserveSlot(WooCommerceUser $wooCommerceUser, Portfolio $portfolio): void
    {
        Redis::zadd(self::reservedKey($wooCommerceUser), now()->getTimestamp(), $portfolio->id);
        Redis::expire(self::reservedKey($wooCommerceUser), self::RESERVED_SLOT_SECONDS);
    }

    public static function releaseReservedSlot(WooCommerceUser $wooCommerceUser, Portfolio $portfolio): void
    {
        Redis::zrem(self::reservedKey($wooCommerceUser), $portfolio->id);
    }

    public static function reservedKey(WooCommerceUser $wooCommerceUser): string
    {
        return 'woo_product_create_reserved_'.$wooCommerceUser->id;
    }

    private static function keepQueue(WooCommerceUser $wooCommerceUser): void
    {
        Redis::expire(self::queueKey($wooCommerceUser), (StoreNewProductToCurrentWooCommerce::RETRY_FOR_HOURS + 1) * 3600);
    }

    /**
     * ponytail: reads the slot keys of Laravel's ConcurrencyLimiter (funnel name + slot number);
     * on a Redis cluster they would be hash-tagged, read them through the limiter if we move to one.
     * A reservation a lost job never took back stops counting after RESERVED_SLOT_SECONDS.
     */
    private static function freeSlots(WooCommerceUser $wooCommerceUser): int
    {
        $funnel = StoreNewProductToCurrentWooCommerce::funnelName($wooCommerceUser);
        $slots  = array_map(fn (int $slot) => $funnel.$slot, range(1, StoreNewProductToCurrentWooCommerce::MAX_CONCURRENT_CREATES_PER_STORE));

        $reserved = self::reservedKey($wooCommerceUser);
        Redis::zremrangebyscore($reserved, '-inf', now()->getTimestamp() - self::RESERVED_SLOT_SECONDS);

        return count(array_filter(Redis::mget($slots), fn ($holder) => !$holder)) - Redis::zcard($reserved);
    }
}
