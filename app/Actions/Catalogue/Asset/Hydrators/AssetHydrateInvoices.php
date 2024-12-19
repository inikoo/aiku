<?php
/*
 * author Arya Permana - Kirin
 * created on 19-12-2024-09h-48m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/


namespace App\Actions\Catalogue\Asset\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Catalogue\Asset;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Support\Carbon;
use Lorisleiva\Actions\Concerns\AsAction;

class AssetHydrateInvoices
{
    use AsAction;
    use WithEnumStats;
    private Asset $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->asset->id))->dontRelease()];
    }

    public function handle(Asset $asset): void
    {
        $now = Carbon::now();
        $dateRanges = [
            'all' => null,
            '1y' => [$now->subYear(), $now],
            '1q' => [$now->subMonths(3), $now],
            '1m' => [$now->subMonth(), $now],
            '1w' => [$now->subWeek(), $now],
            '3d' => [$now->subDays(3), $now],
            '1d' => [$now->subDay(), $now],
            'ytd' => [$now->copy()->startOfYear(), $now],
            'qtd' => [$now->copy()->startOfQuarter(), $now],
            'mtd' => [$now->copy()->startOfMonth(), $now],
            'wtd' => [$now->copy()->startOfWeek(), $now],
            'tdy' => [$now->copy()->startOfDay(), $now],
            'lm' => [$now->copy()->subMonth()->startOfMonth(), $now->copy()->subMonth()->endOfMonth()],
            'lw' => [$now->copy()->subWeek()->startOfWeek(), $now->copy()->subWeek()->endOfWeek()],
            'ld' => [$now->copy()->subDay()->startOfDay(), $now->copy()->subDay()->endOfDay()],

            '1y_ly' => [$now->copy()->subYears(2), $now->copy()->subYear()],
            '1q_ly' => [$now->copy()->subYear()->subMonths(3), $now->copy()->subYear()],
            '1m_ly' => [$now->copy()->subYear()->subMonth(), $now->copy()->subYear()],
            '1w_ly' => [$now->copy()->subYear()->subWeek(), $now->copy()->subYear()],
            '3d_ly' => [$now->copy()->subYear()->subDays(3), $now->copy()->subYear()],
            '1d_ly' => [$now->copy()->subYear()->subDay(), $now->copy()->subYear()],
            'ytd_ly' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            'qtd_ly' => [$now->copy()->subYear()->startOfQuarter(), $now->copy()->subYear()->endOfQuarter()],
            'mtd_ly' => [$now->copy()->subYear()->startOfMonth(), $now->copy()->subYear()->endOfMonth()],
            'wtd_ly' => [$now->copy()->subYear()->startOfWeek(), $now->copy()->subYear()->endOfWeek()],
            'tdy_ly' => [$now->copy()->subYear()->startOfDay(), $now->copy()->subYear()->endOfDay()],
            'lm_ly' => [$now->copy()->subYear()->subMonth()->startOfMonth(), $now->copy()->subYear()->subMonth()->endOfMonth()],
            'lw_ly' => [$now->copy()->subYear()->subWeek()->startOfWeek(), $now->copy()->subYear()->subWeek()->endOfWeek()],
            'ld_ly' => [$now->copy()->subYear()->subDay()->startOfDay(), $now->copy()->subYear()->subDay()->endOfDay()],
        ];

        for ($i = 1; $i <= 5; $i++) {
            $dateRanges["py$i"] = [
                $now->copy()->subYears($i)->startOfYear(),
                $now->copy()->subYears($i)->endOfYear()
            ];
        }
    
        for ($i = 1; $i <= 5; $i++) {
            $dateRanges["pq$i"] = [
                $now->copy()->subQuarters($i)->startOfQuarter(),
                $now->copy()->subQuarters($i)->endOfQuarter()
            ];
        }

        $stats = [];

        foreach ($dateRanges as $key => $range) {
            if ($key === 'all') {
                $invoices = $asset->invoiceTransactions()
                    ->with('invoice')
                    ->get()
                    ->pluck('invoice')
                    ->filter() 
                    ->unique('id'); 
            } else {
                [$start, $end] = $range;
        
                $invoices = $asset->invoiceTransactions()
                    ->with('invoice')
                    ->whereHas('invoice', function ($query) use ($start, $end) {
                        $query->whereBetween('created_at', [$start, $end]);
                    })
                    ->get()
                    ->pluck('invoice')
                    ->filter()
                    ->unique('id');
            }
        
            $stats["invoices_{$key}"] = $invoices->count();
        }
        

        $asset->orderingIntervals()->update($stats);

    }

}
