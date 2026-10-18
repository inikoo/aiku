<?php

/*
 * author Louis Perez
 * created on 31-03-2026-15h-43m
 * github: https://github.com/louis-perez
 * copyright 2026
*/

namespace App\Actions\Inventory\OrgStock\Stock;

use App\Actions\Inventory\OrgStockFamily\Hydrators\OrgStockFamilyHydrateStockValue;
use App\Models\Inventory\OrgStock;
use App\Models\Inventory\OrgStockFamily;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class RunCurrentStockHistory
{
    use AsAction;


    public function handle(): void
    {
        DB::table('location_org_stocks')
            ->select('org_stock_id')
            ->distinct()
            ->orderBy('org_stock_id')
            ->chunkById(100, function ($locationOrgStocks) {
                $orgStocks = OrgStock::with([
                    'organisation.currency',
                    'group.currency',
                    'stats',
                    'orgStockFamily',
                    'locationOrgStocks' => fn ($query) => $query->select(['id', 'org_stock_id', 'location_id', 'quantity'])->orderBy('id'),
                ])
                    ->whereIn('id', $locationOrgStocks->pluck('org_stock_id'))
                    ->orderBy('id')
                    ->get();
                if ($orgStocks->isNotEmpty()) {
                    CalculateOrgStockCurrentStockHistories::make()->calculateOrgStocks($orgStocks);
                }
            }, 'org_stock_id');

        OrgStockFamily::orderBy('id')->chunk(100, function ($orgStockFamilies) {
            foreach ($orgStockFamilies as $orgStockFamily) {
                OrgStockFamilyHydrateStockValue::run($orgStockFamily);
            }
        });
    }

    public function getCommandSignature(): string
    {
        return 'run:current_stock_history';
    }

    public function asCommand(): int
    {
        $this->handle();
        return 0;
    }

}
