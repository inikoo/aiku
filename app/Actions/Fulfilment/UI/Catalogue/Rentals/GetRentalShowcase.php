<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 18 Dec 2024 23:47:01 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\UI\Catalogue\Rentals;

use App\Models\Billables\Rental;
use Illuminate\Http\Resources\Json\JsonResource;
use Lorisleiva\Actions\Concerns\AsObject;

class GetRentalShowcase
{
    use AsObject;

    public function handle(Rental $rental)
    {
        $asset = $rental->asset;
        $data   = [
            'sales'    => JsonResource::make($asset->orderingStats),
            'currency' => $asset->currency,
            'total'    => collect([
                'ytd' => 'sales_org_currency_ytd',
                'qtd' => 'sales_org_currency_qtd',
                'mtd' => 'sales_org_currency_mtd',
                'wtd' => 'sales_org_currency_wtd',
                'lm'  => 'sales_org_currency_lm',
                'lw'  => 'sales_org_currency_lw',
                'ld'  => 'sales_org_currency_ld',
                'tdy' => 'sales_org_currency_tdy',
                '1y'  => 'sales_org_currency_1y',
                '1q'  => 'sales_org_currency_1q',
                '1m'  => 'sales_org_currency_1m',
                '1w'  => 'sales_org_currency_1w',
            ])->mapWithKeys(function ($salesInterval, $key) use ($asset) {
                return [
                    $key => [
                        'total_sales'    => $asset->salesIntervals->$salesInterval ?? 0,
                        'total_invoices' => $asset->orderingIntervals->{"invoices_{$key}"} ?? 0,
                        'total_refunds'  => $asset->orderingIntervals->{"refunds_{$key}"} ?? 0,
                    ]
                ];
            })->toArray() + [
                'all' => [
                    'total_sales'    => $asset->salesIntervals->sales_grp_currency_all ?? 0,
                    'total_invoices' => $asset->orderingIntervals->invoices_all ?? 0,
                    'total_refunds'  => $asset->orderingIntervals->refunds_all ?? 0,
                ],
            ],
            'interval_percentages' => [
                'sales' => $this->mapIntervals(
                    $asset->salesIntervals,
                    'sales',
                    [
                        'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'ld', 'tdy', '1y', '1q', '1m', '1w', 'all'
                    ]
                ),
                'invoices' => $this->mapIntervals(
                    $asset->salesIntervals,
                    'invoices',
                    [
                        'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'ld', 'tdy', '1y', '1q', '1m', '1w', 'all'
                    ]
                ),
                'refunds' => $this->mapIntervals(
                    $asset->salesIntervals,
                    'refunds',
                    [
                        'ytd', 'qtd', 'mtd', 'wtd', 'lm', 'lw', 'ld', 'tdy', '1y', '1q', '1m', '1w', 'all'
                    ]
                )
            ]
        ];
        return $data;
    }

    public function calculatePercentageIncrease($thisYear, $lastYear): ?float
    {
        if ($lastYear == 0) {
            return $thisYear > 0 ? null : 0;
        }

        return (($thisYear - $lastYear) / $lastYear) * 100;
    }

    protected function mapIntervals($intervalData, string $prefix, array $keys): array
    {
        $result = [];
        foreach ($keys as $key) {
            $result[$key] = [
                'amount'     => $intervalData->{$prefix . '_' . $key} ?? null,
                'percentage' => isset($intervalData->{$prefix . '_' . $key}, $intervalData->{$prefix . '_' . $key . '_ly'})
                    ? $this->calculatePercentageIncrease(
                        $intervalData->{$prefix . '_' . $key},
                        $intervalData->{$prefix . '_' . $key . '_ly'}
                    )
                    : null,
                'difference' => isset($intervalData->{$prefix . '_' . $key}, $intervalData->{$prefix . '_' . $key . '_ly'})
                    ? $intervalData->{$prefix . '_' . $key} - $intervalData->{$prefix . '_' . $key . '_ly'}
                    : null,
            ];
        }

        if (isset($result['all'])) {
            $result['all'] = [
                'amount' => $intervalData->{$prefix . '_all'} ?? null,
            ];
        }

        return $result;
    }
}
