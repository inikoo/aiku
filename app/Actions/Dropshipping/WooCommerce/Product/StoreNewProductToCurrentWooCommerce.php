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
use App\Actions\Dropshipping\CustomerSalesChannel\UpdateCustomerSalesChannel;
use App\Actions\Dropshipping\Portfolio\UpdatePortfolio;
use App\Actions\Dropshipping\WooCommerce\CheckWooChannel;
use App\Actions\Dropshipping\WithPortfolioErrorResponse;
use App\Actions\OrgAction;
use App\Enums\Dropshipping\CustomerSalesChannelStateEnum;
use App\Enums\Dropshipping\WooCommerceConnectionFailureEnum;
use App\Enums\Ordering\PlatformLogs\PlatformPortfolioLogsStatusEnum;
use App\Enums\Ordering\PlatformLogs\PlatformPortfolioLogsTypeEnum;
use App\Events\UploadProductToSalesChannelProgressEvent;
use App\Models\Dropshipping\Portfolio;
use App\Models\Dropshipping\WooCommerceUser;
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
     * Counted from when the create is dispatched for a free slot: the wait in DispatchWooUploadQueue
     * does not use it up, as a slow store can take days over a big upload.
     */
    public const int RETRY_FOR_HOURS = 24;

    /**
     * Worded with "timed out" so the nightly RetryTimedOutWooUploads queues the upload again.
     */
    public const string GAVE_UP_WAITING_MESSAGE = 'Timed out waiting for the store to accept more products';

    public int $jobUniqueFor = (self::RETRY_FOR_HOURS + 1) * 3600;

    /**
     * A shared host under a bulk upload can miss one answer and be fine a minute later: the store is
     * paused only after this many connection failures in a row, any upload that gets through resets
     * the count.
     */
    public const int CONNECTION_FAILURES_BEFORE_PAUSE = 3;

    private const int CONNECTION_FAILURES_KEPT_SECONDS = 900;

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
            $probe = $checkConnection ? $wooCommerceUser->probeConnection() : null;

            if ($probe === null || $probe['success']) {
                $portfolio = StoreWooCommerceProduct::run($wooCommerceUser, $portfolio);

                if (!$portfolio->platform_product_id && $probe === null) {
                    $wooCommerceUser->setTimeout(30);
                    $probe = $wooCommerceUser->probeConnection();

                    if (!$probe['success'] && $probe['failure']) {
                        UpdatePortfolio::run($portfolio, [
                            'errors_response' => array_merge($portfolio->errors_response ?? [], ['connection_failure' => $probe['failure']->value])
                        ]);
                    }
                }
            } else {
                $this->recordConnectionFailure($portfolio, $probe['failure']);
            }

            $this->countConnection($wooCommerceUser, $portfolio, $probe);
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
    private function recordConnectionFailure(Portfolio $portfolio, ?WooCommerceConnectionFailureEnum $failure): void
    {
        $message = $failure?->customerMessage() ?? __('Could not connect to your store, please check your channel connection and try again.');

        $log = StorePlatformPortfolioLog::run($portfolio, [
            'type' => PlatformPortfolioLogsTypeEnum::UPLOAD
        ]);

        UpdatePlatformPortfolioLog::run($log, [
            'status'   => PlatformPortfolioLogsStatusEnum::FAIL,
            'response' => $message
        ]);

        UpdatePortfolio::run($portfolio, [
            'errors_response' => array_merge($this->portfolioErrorResponse($message) ?? [], array_filter(['connection_failure' => $failure?->value]))
        ]);
    }

    /**
     * @param array{success: bool, failure: WooCommerceConnectionFailureEnum|null}|null $probe
     */
    private function countConnection(WooCommerceUser $wooCommerceUser, Portfolio $portfolio, ?array $probe): void
    {
        $key = 'woo_connection_failures_'.$wooCommerceUser->id;

        if ($portfolio->platform_product_id) {
            Redis::del($key);

            return;
        }

        if (!$probe || $probe['success'] || !$probe['failure']) {
            return;
        }

        $failures = Redis::incr($key);
        Redis::expire($key, self::CONNECTION_FAILURES_KEPT_SECONDS);

        if ($failures >= self::CONNECTION_FAILURES_BEFORE_PAUSE) {
            Redis::del($key);
            $this->pauseUploads($wooCommerceUser, $probe['failure']);
        }
    }

    /**
     * The channel goes not ready as a failed ping would leave it, so the customer sees why and stock
     * pushes and order fetches skip it too, and its upload list stops until the six hourly ping or
     * the customer's try again finds the store answering; CheckWooChannel then resumes it.
     */
    private function pauseUploads(WooCommerceUser $wooCommerceUser, WooCommerceConnectionFailureEnum $failure): void
    {
        DispatchWooUploadQueue::keepWhilePaused($wooCommerceUser);

        UpdateCustomerSalesChannel::run($wooCommerceUser->customerSalesChannel, array_merge(
            CheckWooChannel::connectionFailureData($failure),
            [
                'platform_status'         => false,
                'can_connect_to_platform' => false,
                'state'                   => CustomerSalesChannelStateEnum::NOT_READY,
            ]
        ));
    }

    /**
     * A job only runs once DispatchWooUploadQueue reserved it a free slot, and frees it to the next
     * in the store's list. A burst of finishes can still start a job for a slot another job then
     * takes; that one keeps its reservation and waits a beat holding its unique lock, as back in the
     * list a finishing create could pop it while the lock is still held and its dispatch would be
     * dropped.
     *
     * $retryUntil is unused but must stay: jobs queued before 22 Oct carry it, and Laravel Actions
     * only passes the job in when asJob takes more parameters than the job was queued with.
     */
    public function asJob(JobDecorator $job, WooCommerceUser $wooCommerceUser, Portfolio $portfolio, bool $checkConnection = true, ?array $bulkProgress = null, ?int $retryUntil = null): void
    {
        if (DispatchWooUploadQueue::isPaused($wooCommerceUser)) {
            DispatchWooUploadQueue::releaseReservedSlot($wooCommerceUser, $portfolio);
            DispatchWooUploadQueue::push($wooCommerceUser, $portfolio, $checkConnection, $bulkProgress);

            return;
        }

        $hadSlot = null;

        try {
            $hadSlot = Redis::funnel(self::funnelName($wooCommerceUser))
                ->limit(self::MAX_CONCURRENT_CREATES_PER_STORE)
                ->releaseAfter(StoreWooCommerceProduct::CREATE_TIMEOUT_SECONDS * 3)
                ->block(0)
                ->then(
                    function () use ($wooCommerceUser, $portfolio, $checkConnection, $bulkProgress) {
                        DispatchWooUploadQueue::releaseReservedSlot($wooCommerceUser, $portfolio);
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
            DispatchWooUploadQueue::reserveSlot($wooCommerceUser, $portfolio);
            $job->release(DispatchWooUploadQueue::CHECK_AGAIN_SECONDS);
        }
    }

    public static function funnelName(WooCommerceUser $wooCommerceUser): string
    {
        return 'woo_product_create_'.$wooCommerceUser->id;
    }

    private function recordGaveUpWaiting(Portfolio $portfolio): void
    {
        UpdatePortfolio::run($portfolio, [
            'errors_response' => ['message' => self::GAVE_UP_WAITING_MESSAGE],
        ]);
    }

    public function getJobRetryUntil(): \DateTimeInterface
    {
        return now()->addHours(self::RETRY_FOR_HOURS);
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
