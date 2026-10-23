<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 17:45:00 Central European Summer Time, Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Actions\GoodsIn\StockDelivery\DeleteStockDelivery;
use App\Enums\GoodsIn\StockDelivery\StockDeliveryStateEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\SysAdmin\Organisation;
use App\Transfers\AuroraOrganisationService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class RepairAuroraDeletedStockDeliveries
{
    use AsAction;

    public string $commandSignature = 'repair:aurora_deleted_stock_deliveries {organisations?*} {--N|dry_run}';
    public string $commandDescription = 'Delete stock deliveries not yet received that were deleted in Aurora but never propagated to aiku';

    private const NOT_RECEIVED_STATES = [
        StockDeliveryStateEnum::IN_PROCESS,
        StockDeliveryStateEnum::CONFIRMED,
        StockDeliveryStateEnum::READY_TO_SHIP,
        StockDeliveryStateEnum::DISPATCHED,
    ];

    public function handle(Organisation $organisation, bool $dryRun = false): array
    {
        $organisationSource = new AuroraOrganisationService();
        $organisationSource->initialisation($organisation);

        $auroraKeys = DB::connection('aurora')
            ->table('Supplier Delivery Dimension')
            ->pluck('Supplier Delivery Key');

        return $this->deleteMissingFromAurora($organisation, $auroraKeys, $dryRun);
    }

    /**
     * Keys above Aurora's highest are left alone: a stale Aurora copy has not seen them yet, so their absence proves nothing.
     *
     * @param  Collection<int, int>  $auroraKeys
     * @return array{organisation: string, deleted: list<string>, skipped: list<string>}
     */
    public function deleteMissingFromAurora(Organisation $organisation, Collection $auroraKeys, bool $dryRun = false): array
    {
        $result = ['organisation' => $organisation->slug, 'deleted' => [], 'skipped' => []];

        if ($auroraKeys->isEmpty()) {
            return $result;
        }

        $existingKeys   = $auroraKeys->map(fn ($key) => (int) $key)->flip();
        $highestKey     = $auroraKeys->max();
        $sourcePrefix   = $organisation->id.':';

        StockDelivery::where('organisation_id', $organisation->id)
            ->where('source_id', 'like', $sourcePrefix.'%')
            ->whereIn('state', self::NOT_RECEIVED_STATES)
            ->chunkById(200, function ($stockDeliveries) use ($existingKeys, $highestKey, $sourcePrefix, $dryRun, &$result) {
                foreach ($stockDeliveries as $stockDelivery) {
                    $key = (int) substr($stockDelivery->source_id, strlen($sourcePrefix));
                    if ($key > $highestKey || $existingKeys->has($key)) {
                        continue;
                    }

                    $label = $stockDelivery->id.' '.$stockDelivery->reference;

                    if ($stockDelivery->items()->where('unit_quantity_placed', '>', 0)->exists()) {
                        $result['skipped'][] = $label.': has placed items';

                        continue;
                    }
                    if ($refusal = DeleteStockDelivery::recordsRefusal($stockDelivery)) {
                        $result['skipped'][] = $label.': '.$refusal;

                        continue;
                    }

                    if (!$dryRun) {
                        DeleteStockDelivery::make()->handle($stockDelivery, 'Deleted in Aurora');
                    }
                    $result['deleted'][] = $label;
                }
            });

        return $result;
    }

    public function asCommand(Command $command): int
    {
        $dryRun = (bool) $command->option('dry_run');

        $query = Organisation::query()
            ->where('type', OrganisationTypeEnum::SHOP->value)
            ->whereNotNull('source');
        if ($command->argument('organisations')) {
            $query->whereIn('slug', $command->argument('organisations'));
        }

        foreach ($query->get() as $organisation) {
            if (!Arr::get($organisation->source, 'db_name')) {
                continue;
            }
            $result = $this->handle($organisation, $dryRun);
            $command->info(sprintf(
                '%s: %d %s, %d skipped',
                $result['organisation'],
                count($result['deleted']),
                $dryRun ? 'would be deleted' : 'deleted',
                count($result['skipped'])
            ));
            foreach ($result['deleted'] as $line) {
                $command->line('  '.($dryRun ? 'would delete ' : 'deleted ').$line);
            }
            foreach ($result['skipped'] as $line) {
                $command->warn('  skipped '.$line);
            }
        }

        return 0;
    }
}
