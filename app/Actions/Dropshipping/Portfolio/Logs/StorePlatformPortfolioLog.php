<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 10 Jun 2024 19:36:21 Central European Summer Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Dropshipping\Portfolio\Logs;

use App\Actions\OrgAction;
use App\Enums\Ordering\PlatformLogs\PlatformPortfolioLogsStatusEnum;
use App\Enums\Ordering\PlatformLogs\PlatformPortfolioLogsTypeEnum;
use App\Models\Dropshipping\PlatformPortfolioLogs;
use App\Models\Dropshipping\Portfolio;
use Illuminate\Support\Arr;

class StorePlatformPortfolioLog extends OrgAction
{
    public function handle(Portfolio $portfolio, array $modelData): PlatformPortfolioLogs
    {
        return PlatformPortfolioLogs::create($this->attributes($portfolio, $modelData));
    }

    /**
     * One insert for many logs, each row as handle() would store it.
     *
     * @param  list<array{0: Portfolio, 1: array}>  $logs  each portfolio with the data its log stores
     */
    public function handleMany(array $logs): void
    {
        $log       = new PlatformPortfolioLogs();
        $timestamp = $log->fromDateTime($log->freshTimestamp());

        $rows = [];
        foreach ($logs as [$portfolio, $modelData]) {
            $row = (new PlatformPortfolioLogs())->forceFill(['response' => null, ...$this->attributes($portfolio, $modelData)])->getAttributes();
            ksort($row);
            $rows[] = [...$row, 'created_at' => $timestamp, 'updated_at' => $timestamp];
        }

        if ($rows) {
            PlatformPortfolioLogs::insert($rows);
        }
    }

    private function attributes(Portfolio $portfolio, array $modelData): array
    {
        data_set($modelData, 'group_id', $portfolio->group_id);
        data_set($modelData, 'organisation_id', $portfolio->organisation_id);
        data_set($modelData, 'shop_id', $portfolio->shop_id);
        data_set($modelData, 'customer_id', $portfolio->customer_id);
        data_set($modelData, 'customer_sales_channel_id', $portfolio->customer_sales_channel_id);
        data_set($modelData, 'portfolio_id', $portfolio->id);
        data_set($modelData, 'platform_id', $portfolio->platform_id);
        data_set($modelData, 'platform_type', $portfolio->customerSalesChannel->platform->type);

        if (!Arr::exists($modelData, 'status')) {
            data_set($modelData, 'status', PlatformPortfolioLogsStatusEnum::PROCESSING);
        }

        if (!Arr::exists($modelData, 'type')) {
            data_set($modelData, 'type', PlatformPortfolioLogsTypeEnum::UPDATE_STOCK);
        }

        return $modelData;
    }

}
