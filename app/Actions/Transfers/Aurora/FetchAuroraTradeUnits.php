<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 22 Sept 2022 02:28:55 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\Goods\TradeUnit\StoreTradeUnit;
use App\Actions\Goods\TradeUnit\UpdateTradeUnit;
use App\Models\Goods\TradeUnit;
use App\Models\Helpers\Barcode;
use App\Transfers\SourceOrganisationService;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class FetchAuroraTradeUnits extends FetchAuroraAction
{
    public string $commandSignature = 'fetch:trade-units {organisations?*} {--s|source_id=} {--d|db_suffix=}';

    public function handle(SourceOrganisationService $organisationSource, int $organisationSourceId): ?TradeUnit
    {
        if ($tradeUnitData = $organisationSource->fetchTradeUnit($organisationSourceId)) {
            if (TradeUnit::withTrashed()->where('source_slug', $tradeUnitData['trade_unit']['source_slug'])->exists()) {
                if ($tradeUnit = TradeUnit::withTrashed()->where('source_id', $tradeUnitData['trade_unit']['source_id'])->first()) {
                    try {
                        $tradeUnit = UpdateTradeUnit::make()->action(
                            tradeUnit: $tradeUnit,
                            modelData: $tradeUnitData['trade_unit'],
                            hydratorsDelay: 30,
                            strict: false,
                            audit: false
                        );
                        $this->recordChange($organisationSource, $tradeUnit->wasChanged());
                    } catch (Exception $e) {
                        $this->recordError($organisationSource, $e, $tradeUnitData['trade_unit'], 'TradeUnit', 'update');

                        return null;
                    }
                }
            } else {
                try {
                    $tradeUnit = StoreTradeUnit::make()->action(
                        group: $organisationSource->getOrganisation()->group,
                        modelData: $tradeUnitData['trade_unit'],
                        hydratorsDelay: 30,
                        strict: false,
                        audit: false
                    );
                    TradeUnit::enableAuditing();
                    $this->saveMigrationHistory(
                        $tradeUnit,
                        Arr::except($tradeUnitData['trade_unit'], ['fetched_at', 'last_fetched_at', 'source_id'])
                    );

                    $this->recordNew($organisationSource);
                } catch (Exception|Throwable $e) {
                    $this->recordError($organisationSource, $e, $tradeUnitData['trade_unit'], 'TradeUnit', 'store');

                    return null;
                }
            }


            if (!$tradeUnit) {
                $tradeUnit = TradeUnit::withTrashed()->where('source_slug', $tradeUnitData['trade_unit']['source_slug'])->first();
            }

            if ($tradeUnit) {
                $this->updateTradeUnitSources($tradeUnit, $tradeUnitData['trade_unit']['source_id']);

                $tradeUnit->barcodes()->sync(
                    $tradeUnitData['barcodes']
                );

                $barcodeId     = null;
                $barcodeNumber = null;

                foreach ($tradeUnitData['barcodes'] as $barcodeKey => $barcodeData) {
                    if ($barcodeData['status']) {
                        /** @var Barcode $barcode */
                        $barcode = Barcode::find($barcodeKey);

                        $barcodeId     = $barcode->id;
                        $barcodeNumber = $barcode->number;
                        break;
                    }
                }

                $tradeUnit->updateQuietly([
                    'barcode_id'     => $barcodeId,
                    'barcode' => $barcodeNumber,
                ]);

            }


            return $tradeUnit;
        }


        return null;
    }


    public function updateTradeUnitSources(TradeUnit $tradeUnit, string $source): void
    {
        $sources   = Arr::get($tradeUnit->sources, 'parts', []);
        $sources[] = $source;
        $sources   = array_unique($sources);

        $tradeUnit->updateQuietly([
            'sources' => [
                'parts' => $sources,
            ]
        ]);
    }

    public function getModelsQuery(): Builder
    {
        $query = DB::connection('aurora')
            ->table('Part Dimension')
            ->select('Part SKU as source_id');

        $query->orderBy('Part Valid From');

        return $query;
    }

    public function count(): ?int
    {
        $query = DB::connection('aurora')->table('Part Dimension');

        return $query->count();
    }


}
