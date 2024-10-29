<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Mon, 05 Sept 2022 00:35:52 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Goods\Stock\StoreStock;
use App\Actions\Goods\Stock\SyncStockTradeUnits;
use App\Actions\Goods\Stock\UpdateStock;
use App\Actions\Inventory\OrgStock\SyncOrgStockLocations;
use App\Enums\SupplyChain\Stock\StockStateEnum;
use App\Models\Goods\TradeUnit;
use App\Models\SupplyChain\Stock;
use App\Transfers\Aurora\WithAuroraAttachments;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraStocks extends FetchAuroraAction
{
    use WithAuroraAttachments;
    use HasStockLocationsFetch;
    use WithFetchStock;


    public string $commandSignature = 'fetch:stocks {organisations?*} {--s|source_id=} {--N|only_new : Fetch only new} {--d|db_suffix=} {--r|reset}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): array
    {
        $orgStock       = null;
        $effectiveStock = null;

        if ($stockData = $organisationSource->fetchStock($organisationSourceId)) {
            if ($baseStock = Stock::withTrashed()->where('source_slug', $stockData['stock']['source_slug'])->first()) {
                if ($stock = Stock::withTrashed()->where('source_id', $stockData['stock']['source_id'])->first()) {
                    try {
                        $stock = UpdateStock::make()->action(
                            stock: $stock,
                            modelData: $stockData['stock'],
                            hydratorsDelay: 60,
                            strict: false,
                            audit: false
                        );
                        $this->recordChange($organisationSource, $stock->wasChanged());
                    } catch (Exception $e) {
                        $this->recordError($organisationSource, $e, $stockData['stock'], 'Stock', 'update');

                        return [
                            'stock'    => null,
                            'orgStock' => null
                        ];
                    }
                }
            } else {
                if ($stockData['stock_family']) {
                    $parent = $stockData['stock_family'];
                } else {
                    $parent = $organisationSource->getOrganisation()->group;
                }
                try {
                    $stock = StoreStock::make()->action(
                        parent: $parent,
                        modelData: $stockData['stock'],
                        hydratorsDelay: 60,
                        strict: false,
                        audit: false
                    );
                    Stock::enableAuditing();
                    $this->saveMigrationHistory(
                        $stock,
                        Arr::except($stockData['stock'], ['fetched_at', 'last_fetched_at', 'source_id'])
                    );

                    $this->updateStockSources($stock, $stockData['stock']['source_id']);

                    $this->recordNew($organisationSource);

                    $sourceData = explode(':', $stock->source_id);
                    DB::connection('aurora')->table('Part Dimension')
                        ->where('Part SKU', $sourceData[1])
                        ->update(['aiku_id' => $stock->id]);
                } catch (Exception|Throwable $e) {
                    $this->recordError($organisationSource, $e, $stockData['stock'], 'Stock', 'store');

                    return [
                        'stock'    => null,
                        'orgStock' => null
                    ];
                }
            }


            if ($stock) {
                $tradeUnit = $stockData['trade_unit'];

                SyncStockTradeUnits::run($stock, [
                    $tradeUnit->id => [
                        'quantity' => $stockData['stock']['units_per_pack']
                    ]
                ]);
            }

            $effectiveStock = $stock ?? $baseStock;

            if ($effectiveStock and $effectiveStock->state == StockStateEnum::IN_PROCESS and $stockData['org_stock']['state'] != StockStateEnum::IN_PROCESS) {
                $effectiveStock = UpdateStock::make()->action(
                    stock: $effectiveStock,
                    modelData: [
                        'state' => StockStateEnum::ACTIVE
                    ],
                    audit: false
                );
            }

            if ($effectiveStock and $effectiveStock->state != StockStateEnum::IN_PROCESS) {
                $orgStock = $this->processOrgStock($organisationSource, $effectiveStock, $stockData);

                if ($orgStock) {
                    $locationsData = $this->getStockLocationData($organisationSource, $stockData['stock']['source_id']);
                    SyncOrgStockLocations::make()->action($orgStock, [
                        'locationsData' => $locationsData
                    ], 60, false);
                }

            }
        }


        if ($effectiveStock) {
            /** @var TradeUnit $tradeUnit */
            $tradeUnit = $effectiveStock->tradeUnits()->first();
            $this->processFetchAttachments($tradeUnit, 'Part', $stockData['stock']['source_id']);
        }

        return [
            'stock'    => $effectiveStock,
            'orgStock' => $orgStock
        ];
    }


    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Part Dimension')
            ->select('Part SKU as source_id')
            ->when(app()->environment('testing'), function ($query) {
                return $query->limit(20);
            });

        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }
        $query->orderBy('Part Valid From');

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Part Dimension');
        if ($this->onlyNew) {
            $query->whereNull('aiku_id');
        }

        return $query->count();
    }

    public function reset(): void
    {
        DB::connection('aurora')->table('Part Dimension')->update(['aiku_id' => null]);
    }
}
