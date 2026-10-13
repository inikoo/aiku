<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 13 Oct 2026 22:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Transfers\Aurora;

use App\Enums\GoodsIn\StockDelivery\StockDeliveryStateEnum;
use App\Enums\GoodsIn\StockDeliveryItem\StockDeliveryItemStateEnum;
use App\Enums\SysAdmin\Organisation\OrganisationTypeEnum;
use App\Models\GoodsIn\StockDelivery;
use App\Models\SysAdmin\Organisation;
use App\Transfers\AuroraOrganisationService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

class RepairAuroraStockDeliveryCosting
{
    use AsAction;

    public string $commandSignature = 'repair:aurora_stock_delivery_costing {organisations?*} {--N|dry_run}';

    public string $commandDescription = 'Mark as costed the placed stock deliveries that were costed in Aurora and never costed in aiku';

    private const string AURORA_COSTED_STATE = 'InvoiceChecked';

    public function handle(Organisation $organisation, bool $dryRun = false): int
    {
        $organisationSource = new AuroraOrganisationService();
        $organisationSource->initialisation($organisation);

        $auroraStates = DB::connection('aurora')
            ->table('Supplier Delivery Dimension')
            ->pluck('Supplier Delivery State', 'Supplier Delivery Key');

        return $this->markCostedInAurora($organisation, $auroraStates, $dryRun);
    }

    /**
     * Sets the flag only: amounts, cost rows and stock values stay as Aurora left them. A delivery with an
     * amount on its aiku cost checklist is being costed in aiku and is left alone.
     *
     * @param  Collection<int|string, string>  $auroraStates  Aurora state by supplier delivery key
     */
    public function markCostedInAurora(Organisation $organisation, Collection $auroraStates, bool $dryRun = false): int
    {
        $fixed = 0;

        StockDelivery::where('organisation_id', $organisation->id)
            ->where('state', StockDeliveryStateEnum::PLACED)
            ->where('is_costed', false)
            ->whereNotNull('source_id')
            ->whereDoesntHave('costs', fn ($query) => $query->whereNotNull('amount'))
            ->chunkById(200, function ($stockDeliveries) use ($auroraStates, $dryRun, &$fixed) {
                foreach ($stockDeliveries as $stockDelivery) {
                    $sourceKey = explode(':', $stockDelivery->source_id)[1] ?? null;
                    if (!$sourceKey || $auroraStates->get($sourceKey) !== self::AURORA_COSTED_STATE) {
                        continue;
                    }

                    if (!$dryRun) {
                        $stockDelivery->updateQuietly(['is_costed' => true]);
                        $stockDelivery->items()
                            ->where('state', '!=', StockDeliveryItemStateEnum::CANCELLED)
                            ->update(['is_costed' => true]);
                    }
                    $fixed++;
                }
            });

        return $fixed;
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

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
            $command->info(sprintf(
                '%s: %d stock deliveries %s',
                $organisation->slug,
                $this->handle($organisation, $dryRun),
                $dryRun ? 'would be marked as costed' : 'marked as costed'
            ));
        }

        return 0;
    }
}
