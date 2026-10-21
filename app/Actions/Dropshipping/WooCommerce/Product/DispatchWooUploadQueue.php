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

    public static function push(WooCommerceUser $wooCommerceUser, Portfolio $portfolio, bool $checkConnection = true, ?array $bulkProgress = null, ?int $retryUntil = null): void
    {
        $queue = self::queueKey($wooCommerceUser);
        $entry = json_encode([
            'portfolio_id'     => $portfolio->id,
            'check_connection' => $checkConnection,
            'bulk_progress'    => $bulkProgress,
            'retry_until'      => $retryUntil ?? now()->addHours(StoreNewProductToCurrentWooCommerce::RETRY_FOR_HOURS)->getTimestamp(),
        ]);

        Redis::rpush($queue, $entry);
        Redis::expire($queue, (StoreNewProductToCurrentWooCommerce::RETRY_FOR_HOURS + 1) * 3600);
    }

    /**
     * An upload that waited past its retry window ends with the timed out message, so the nightly
     * RetryTimedOutWooUploads queues it again, and still counts in the bulk progress.
     */
    public function handle(WooCommerceUser $wooCommerceUser): void
    {
        $queue     = self::queueKey($wooCommerceUser);
        $freeSlots = self::freeSlots($wooCommerceUser);

        while ($freeSlots > 0 && $entry = Redis::lpop($queue)) {
            $entry     = json_decode($entry, true);
            $portfolio = Portfolio::find($entry['portfolio_id']);

            if (!$portfolio) {
                continue;
            }

            if ($entry['retry_until'] <= now()->getTimestamp()) {
                StoreNewProductToCurrentWooCommerce::make()->giveUpWaiting($wooCommerceUser, $portfolio, $entry['bulk_progress']);

                continue;
            }

            StoreNewProductToCurrentWooCommerce::dispatch($wooCommerceUser, $portfolio, $entry['check_connection'], $entry['bulk_progress'], $entry['retry_until']);
            $freeSlots--;
        }

        if (Redis::llen($queue)) {
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

    /**
     * ponytail: reads the slot keys of Laravel's ConcurrencyLimiter (funnel name + slot number);
     * on a Redis cluster they would be hash-tagged, read them through the limiter if we move to one.
     * A create job dispatched but not yet started holds no slot, so a burst of finishes can start a
     * job too many; it finds the funnel full and waits a beat.
     */
    private static function freeSlots(WooCommerceUser $wooCommerceUser): int
    {
        $funnel = StoreNewProductToCurrentWooCommerce::funnelName($wooCommerceUser);
        $slots  = array_map(fn (int $slot) => $funnel.$slot, range(1, StoreNewProductToCurrentWooCommerce::MAX_CONCURRENT_CREATES_PER_STORE));

        return count(array_filter(Redis::mget($slots), fn ($holder) => !$holder));
    }
}
