<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 26 Aug 2024 14:04:18 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\WooCommerce\Product;

use App\Actions\Traits\Authorisations\WithCustomerSalesChannelEditAuthorisation;
use App\Actions\Dropshipping\Portfolio\Logs\StorePlatformPortfolioLog;
use App\Actions\Dropshipping\Portfolio\Logs\UpdatePlatformPortfolioLog;
use App\Actions\Dropshipping\Portfolio\UpdatePortfolio;
use App\Actions\Dropshipping\WithPortfolioErrorResponse;
use App\Actions\OrgAction;
use App\Enums\Ordering\PlatformLogs\PlatformPortfolioLogsStatusEnum;
use App\Enums\Ordering\PlatformLogs\PlatformPortfolioLogsTypeEnum;
use App\Events\UploadProductToSalesChannelProgressEvent;
use App\Models\Dropshipping\Portfolio;
use App\Models\Dropshipping\WooCommerceUser;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Queue\MaxAttemptsExceededException;
use Illuminate\Support\Facades\Redis;
use Lorisleiva\Actions\ActionRequest;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Decorators\JobDecorator;

class StoreNewProductToCurrentWooCommerce extends OrgAction implements ShouldBeUnique
{
    use WithCustomerSalesChannelEditAuthorisation;
    use WithPortfolioErrorResponse;
    use AsAction;

    public string $jobQueue = 'woo';

    public bool $jobDeleteWhenMissingModels = true;

    public int $jobMaxExceptions = 2;

    /**
     * A shared host serves few requests at once and each create downloads every image, so a bulk
     * upload sending one request per worker stalls the store past the timeout and holds the shared
     * dropshipping workers meanwhile. ponytail: fixed cap for every store, per-channel setting if a
     * host needs lower.
     */
    public const int MAX_CONCURRENT_CREATES_PER_STORE = 4;

    /**
     * At 4 creates at a time a store takes ~750 products an hour, so an 8k bulk upload waits in
     * DispatchWooUploadQueue for ~11h, and an upload's window must outlast the whole upload.
     */
    public const int RETRY_FOR_HOURS = 24;

    /**
     * Worded with "timed out" so the nightly RetryTimedOutWooUploads queues the upload again.
     */
    public const string GAVE_UP_WAITING_MESSAGE = 'Timed out waiting for the store to accept more products';

    public int $jobUniqueFor = (self::RETRY_FOR_HOURS + 1) * 3600;

    public function getJobUniqueId(WooCommerceUser $wooCommerceUser, Portfolio $portfolio): string
    {
        return $portfolio->id;
    }

    /**
     * @throws \Exception
     */
    public function handle(WooCommerceUser $wooCommerceUser, Portfolio $portfolio, bool $checkConnection = true, ?array $bulkProgress = null): Portfolio
    {
        try {
            $connection = $checkConnection ? $wooCommerceUser->checkConnectionWithError() : ['success' => true, 'message' => null];

            if ($connection['success']) {
                $portfolio = StoreWooCommerceProduct::run($wooCommerceUser, $portfolio);
            } else {
                $wooCommerceUser->customerSalesChannel->update([
                    'ban_stock_update_util' => now()->addSeconds(10)
                ]);
                $this->recordConnectionFailure($portfolio, $connection['message'] ?? __('Could not connect to your store, please check your channel connection and try again.'));
            }
        } catch (\Throwable $e) {
            $this->recordPortfolioUploadFailure($portfolio, $e);

            if (!$bulkProgress) {
                throw $e;
            }
        }

        if ($bulkProgress) {
            $this->broadcastBulkProgress($wooCommerceUser, $portfolio, $bulkProgress);
        }

        return $portfolio;
    }

    /**
     * Must not say "timed out": the nightly RetryTimedOutWooUploads would queue it again while the
     * store is still turning us away.
     */
    private function recordConnectionFailure(Portfolio $portfolio, string $message): void
    {
        $log = StorePlatformPortfolioLog::run($portfolio, [
            'type' => PlatformPortfolioLogsTypeEnum::UPLOAD
        ]);

        UpdatePlatformPortfolioLog::run($log, [
            'status'   => PlatformPortfolioLogsStatusEnum::FAIL,
            'response' => $message
        ]);

        UpdatePortfolio::run($portfolio, [
            'errors_response' => $this->portfolioErrorResponse($message)
        ]);
    }

    /**
     * A job only runs once DispatchWooUploadQueue found it a free slot, and frees it to the next in
     * the store's list. A burst of finishes can start a job for a slot another job then takes; that
     * one waits a beat holding its unique lock, as back in the list a finishing create could pop it
     * while the lock is still held and its dispatch would be dropped.
     */
    public function asJob(JobDecorator $job, WooCommerceUser $wooCommerceUser, Portfolio $portfolio, bool $checkConnection = true, ?array $bulkProgress = null, ?int $retryUntil = null): void
    {
        $hadSlot = null;

        try {
            $hadSlot = Redis::funnel(self::funnelName($wooCommerceUser))
                ->limit(self::MAX_CONCURRENT_CREATES_PER_STORE)
                ->releaseAfter(StoreWooCommerceProduct::CREATE_TIMEOUT_SECONDS * 3)
                ->block(0)
                ->then(
                    function () use ($wooCommerceUser, $portfolio, $checkConnection, $bulkProgress) {
                        $this->handle($wooCommerceUser, $portfolio, $checkConnection, $bulkProgress);

                        return true;
                    },
                    fn () => false
                );
        } finally {
            if ($hadSlot !== false) {
                DispatchWooUploadQueue::run($wooCommerceUser);
            }
        }

        if (!$hadSlot) {
            $job->release(DispatchWooUploadQueue::CHECK_AGAIN_SECONDS);
        }
    }

    public static function funnelName(WooCommerceUser $wooCommerceUser): string
    {
        return 'woo_product_create_'.$wooCommerceUser->id;
    }

    public function giveUpWaiting(WooCommerceUser $wooCommerceUser, Portfolio $portfolio, ?array $bulkProgress): void
    {
        $this->recordGaveUpWaiting($portfolio);

        if ($bulkProgress) {
            $this->broadcastBulkProgress($wooCommerceUser, $portfolio->refresh(), $bulkProgress);
        }
    }

    private function recordGaveUpWaiting(Portfolio $portfolio): void
    {
        UpdatePortfolio::run($portfolio, [
            'errors_response' => ['message' => self::GAVE_UP_WAITING_MESSAGE],
        ]);
    }

    /**
     * An upload that waited in the store's list keeps the window it was given when it was queued.
     */
    public function getJobRetryUntil(WooCommerceUser $wooCommerceUser, Portfolio $portfolio, bool $checkConnection = true, ?array $bulkProgress = null, ?int $retryUntil = null): \DateTimeInterface
    {
        return $retryUntil ? Carbon::createFromTimestamp($retryUntil) : now()->addHours(self::RETRY_FOR_HOURS);
    }

    /**
     * A job the queue kills (timeout, lost worker) never reaches the broadcast in handle, and the
     * page would wait for ever; count it as a failure so the progress can still complete. Only a
     * job the queue gave up on says it timed out: any other failure was recorded by handle, and
     * that error must stay or the nightly retry would queue a product that can never upload.
     */
    public function jobFailed(\Throwable $e, WooCommerceUser $wooCommerceUser, Portfolio $portfolio, bool $checkConnection = true, ?array $bulkProgress = null): void
    {
        $portfolio = $portfolio->fresh() ?? $portfolio;

        if ($e instanceof MaxAttemptsExceededException && !$portfolio->platform_product_id) {
            $this->recordGaveUpWaiting($portfolio);
        }

        if ($bulkProgress) {
            $this->broadcastBulkProgress($wooCommerceUser, $portfolio, $bulkProgress);
        }
    }

    public function broadcastBulkProgress(WooCommerceUser $wooCommerceUser, Portfolio $portfolio, array $bulkProgress): void
    {
        $cacheKey = $bulkProgress['cache_key'];
        Cache::increment($cacheKey.($portfolio->platform_status ? '_success' : '_fail'));

        UploadProductToSalesChannelProgressEvent::dispatch($wooCommerceUser->customerSalesChannel, $portfolio, [
            'total'   => $bulkProgress['total'],
            'success' => (int) Cache::get($cacheKey.'_success'),
            'fail'    => (int) Cache::get($cacheKey.'_fail'),
        ]);
    }

    /**
     * @throws \Exception
     */
    public function asController(Portfolio $portfolio, ActionRequest $request): void
    {
        /** @var WooCommerceUser $wooCommerceUser */
        $wooCommerceUser = $portfolio->customerSalesChannel->user;
        $this->initialisation($portfolio->organisation, $request);

        $this->handle($wooCommerceUser, $portfolio);
    }
}
