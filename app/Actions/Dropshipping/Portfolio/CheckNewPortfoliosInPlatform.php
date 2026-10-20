<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Portfolio;

use App\Actions\Dropshipping\CustomerSalesChannel\Hydrators\CustomerSalesChannelsHydratePortfolios;
use App\Events\CloneRetinaPortfolioProgressEvent;
use App\Models\Dropshipping\CustomerSalesChannel;
use App\Models\Dropshipping\Portfolio;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Sentry;

class CheckNewPortfoliosInPlatform
{
    use AsAction;

    public const string PROGRESS_ACTION_TYPE = 'CheckPortfolios';

    public string $jobQueue = 'ds';

    public int $jobTimeout = 1800;

    /**
     * @param  array<int>  $portfolioIds
     */
    public function handle(CustomerSalesChannel $customerSalesChannel, array $portfolioIds): void
    {
        $customerSalesChannel->loadMissing(['platform', 'user']);
        $platformType = $customerSalesChannel->platform->type;
        $webUserIds   = $customerSalesChannel->customer->webUsers()->pluck('id')->all();

        $portfolios = Portfolio::whereIn('id', $portfolioIds)->where('customer_sales_channel_id', $customerSalesChannel->id)->orderBy('id')->get();
        $total      = $portfolios->count();
        $done       = 0;
        $fails      = 0;

        foreach ($portfolios as $portfolio) {
            $portfolio->setRelation('customerSalesChannel', $customerSalesChannel);
            try {
                StorePortfolio::checkPortfolioInPlatform($platformType, $portfolio);
            } catch (\Throwable $e) {
                $fails++;
                Sentry::captureException($e);
            }
            $done++;

            if ($done % 10 === 0 && $done < $total) {
                $this->broadcastProgress($webUserIds, $customerSalesChannel->id, $total, $done, $fails);
            }
        }

        CustomerSalesChannelsHydratePortfolios::run($customerSalesChannel);

        $this->broadcastProgress($webUserIds, $customerSalesChannel->id, $total, $done, $fails);
    }

    /**
     * @param  array<int>  $webUserIds
     */
    private function broadcastProgress(array $webUserIds, int $customerSalesChannelId, int $total, int $done, int $fails): void
    {
        foreach ($webUserIds as $webUserId) {
            try {
                CloneRetinaPortfolioProgressEvent::dispatch($webUserId, $customerSalesChannelId, self::PROGRESS_ACTION_TYPE, $total, $done, $done - $fails, $fails);
            } catch (\Throwable $e) {
                Log::warning('Portfolio check progress broadcast failed: '.$e->getMessage());
            }
        }
    }
}
