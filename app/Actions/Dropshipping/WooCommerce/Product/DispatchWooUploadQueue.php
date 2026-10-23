<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\WooCommerce\Product;

use App\Actions\Dropshipping\CustomerSalesChannel\UpdateCustomerSalesChannel;
use App\Actions\Dropshipping\WooCommerce\CheckWooChannel;
use App\Actions\Traits\AsUniqueUntilProcessingAction;
use App\Enums\Ordering\PlatformLogs\PlatformPortfolioLogsTypeEnum;
use App\Models\Dropshipping\CustomerSalesChannel;
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

    /**
     * A store that does not answer pauses its uploads instead of failing them one by one, each after
     * a minute of waiting on the store. Its list is kept this long for the store to come back: an
     * upload the customer asked for weeks ago must not suddenly appear in their store.
     */
    public const int PAUSED_KEEP_DAYS = 7;

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
        $queue = self::queueKey($wooCommerceUser);

        if (self::isPaused($wooCommerceUser)) {
            return;
        }

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

    /**
     * Counted from the pause: uploads asked for while paused go in the same list and do not keep it
     * alive any longer.
     */
    public static function keepWhilePaused(WooCommerceUser $wooCommerceUser): void
    {
        Redis::expire(self::queueKey($wooCommerceUser), self::PAUSED_KEEP_DAYS * 86400);
    }

    /**
     * A parked channel only answers again for its uploads: reviving it would import every order the
     * store still shows as processing, so orders and stock stay off until a person runs woo:check.
     */
    public static function unpause(WooCommerceUser $wooCommerceUser): void
    {
        UpdateCustomerSalesChannel::run($wooCommerceUser->customerSalesChannel, array_merge(
            CheckWooChannel::connectionFailureData(null),
            ['can_connect_to_platform' => true]
        ));

        self::resume($wooCommerceUser);
    }

    /**
     * The store answers again: the uploads that could not connect in the last days go back in the
     * list behind the ones that waited there.
     */
    public static function resume(WooCommerceUser $wooCommerceUser): void
    {
        $queue   = self::queueKey($wooCommerceUser);
        $waiting = collect(Redis::lrange($queue, 0, -1))->map(fn (string $entry) => json_decode($entry, true)['portfolio_id'])->flip();

        $wooCommerceUser->customerSalesChannel->portfolios()
            ->where('status', true)
            ->whereNull('platform_product_id')
            ->whereNotNull('errors_response->connection_failure')
            ->whereExists(fn ($logs) => $logs->selectRaw('1')
                ->from('platform_portfolio_logs')
                ->whereColumn('platform_portfolio_logs.portfolio_id', 'portfolios.id')
                ->where('platform_portfolio_logs.type', PlatformPortfolioLogsTypeEnum::UPLOAD->value)
                ->where('platform_portfolio_logs.created_at', '>=', now()->subDays(self::PAUSED_KEEP_DAYS)))
            ->each(function (Portfolio $portfolio) use ($wooCommerceUser, $waiting) {
                if (!$waiting->has($portfolio->id)) {
                    self::push($wooCommerceUser, $portfolio);
                }
            });

        if (Redis::llen(self::queueKey($wooCommerceUser))) {
            self::run($wooCommerceUser);
        }
    }

    public static function isPaused(WooCommerceUser $wooCommerceUser): bool
    {
        return !CustomerSalesChannel::whereKey($wooCommerceUser->customer_sales_channel_id)->value('can_connect_to_platform');
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
        $queue   = self::queueKey($wooCommerceUser);
        $seconds = (StoreNewProductToCurrentWooCommerce::RETRY_FOR_HOURS + 1) * 3600;

        if (Redis::ttl($queue) < $seconds) {
            Redis::expire($queue, $seconds);
        }
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
