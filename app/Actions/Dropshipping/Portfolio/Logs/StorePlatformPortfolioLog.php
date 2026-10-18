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
     * One insert for many portfolios, each row as handle() would store it with no extra data.
     *
     * @param  iterable<Portfolio>  $portfolios
     * @return array<int, PlatformPortfolioLogs>  portfolio id => its new log
     */
    public function handleMany(iterable $portfolios): array
    {
        $log       = new PlatformPortfolioLogs();
        $timestamp = $log->fromDateTime($log->freshTimestamp());

        $rows = [];
        foreach ($portfolios as $portfolio) {
            $row = $this->attributes($portfolio, []);
            foreach ($row as $column => $value) {
                if ($value instanceof \BackedEnum) {
                    $row[$column] = $value->value;
                }
            }
            $rows[] = [...$row, 'updated_at' => $timestamp, 'created_at' => $timestamp];
        }

        if (!$rows) {
            return [];
        }

        $query      = PlatformPortfolioLogs::query()->toBase();
        $connection = $query->getConnection();
        $inserted   = $connection->selectFromWriteConnection(
            $query->getGrammar()->compileInsert($query, $rows).' returning *',
            $query->cleanBindings(Arr::flatten($rows, 1))
        );
        $connection->recordsHaveBeenModified();

        return PlatformPortfolioLogs::hydrate($inserted)->keyBy('portfolio_id')->all();
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
