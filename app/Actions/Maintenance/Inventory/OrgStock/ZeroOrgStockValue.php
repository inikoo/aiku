<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Oct 2026 00:30:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Maintenance\Inventory\OrgStock;

use App\Actions\Inventory\OrganisationStockHistory\Hydrators\OrganisationStockHistoryHydrateFromOrgStockHistories;
use App\Actions\Inventory\OrgStock\HydrateOrgStock;
use App\Actions\Inventory\OrgStockMovement\CalculateOrgStockMovementRunningValues;
use App\Actions\Traits\WithStockHistoryArchiveWrite;
use App\Models\Inventory\OrgStock;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * HELP-4274. An org stock that stands in for whatever the warehouse grabs (GR-Bonus, Gold Reward
 * Bonus: never bought, sold at £0, topped up by "found" at a typed-in £3.28) carries no value of
 * its own. Its movements keep their quantities and lose their amounts, copied first to
 * help4274_zeroed_org_stock_movements. Its daily histories are set to no value (a recalculation
 * would keep the last price the history already holds), then its running values and the
 * organisation and group stock value days are worked out again.
 */
class ZeroOrgStockValue
{
    use AsAction;
    use WithStockHistoryArchiveWrite;

    private const string BACKUP_TABLE = 'help4274_zeroed_org_stock_movements';

    private const array NO_VALUE = [
        'org_stock_lpp_value'  => 0,
        'grp_stock_lpp_value'  => 0,
        'org_stock_wac_value'  => 0,
        'grp_stock_wac_value'  => 0,
        'org_stock_fifo_value' => 0,
        'grp_stock_fifo_value' => 0,
    ];

    public function getCommandSignature(): string
    {
        return 'org_stock:zero_value {org_stock : org stock id} {--dry-run}';
    }

    /**
     * @return array{movements_with_value: int, total_org_amount: float, organisation_days_rolled_up: int}
     */
    public function handle(OrgStock $orgStock, bool $dryRun): array
    {
        $valued = DB::table('org_stock_movements')
            ->where('org_stock_id', $orgStock->id)
            ->where(fn ($query) => $query->where('org_amount', '!=', 0)->orWhere('grp_amount', '!=', 0)->orWhere('cost_per_sku', '!=', 0));

        $counts = [
            'movements_with_value'        => $valued->clone()->count(),
            'total_org_amount'            => (float)$valued->clone()->sum('org_amount'),
            'organisation_days_rolled_up' => 0,
        ];

        if ($dryRun) {
            return $counts;
        }

        DB::transaction(function () use ($orgStock, $valued) {
            DB::statement('create table if not exists '.self::BACKUP_TABLE.' (like org_stock_movements)');
            DB::statement('insert into '.self::BACKUP_TABLE.' select * from org_stock_movements where org_stock_id = ? and id not in (select id from '.self::BACKUP_TABLE.')', [$orgStock->id]);
            $valued->clone()->update(['org_amount' => 0, 'grp_amount' => 0, 'cost_per_sku' => 0]);
        });

        CalculateOrgStockMovementRunningValues::run($orgStock->id);

        $organisationStockHistoryIds = collect();
        foreach ($this->stockHistoryWriteConnections() as $connection) {
            DB::connection($connection)->table('org_stock_histories')->where('org_stock_id', $orgStock->id)->update(self::NO_VALUE + ['lpp_per_sku' => 0, 'wac_per_sku' => 0, 'fifo_per_sku' => 0]);
            DB::connection($connection)->table('location_org_stock_histories')->where('org_stock_id', $orgStock->id)->update(self::NO_VALUE);
            $organisationStockHistoryIds = $organisationStockHistoryIds->concat(
                DB::connection($connection)->table('org_stock_histories')->where('org_stock_id', $orgStock->id)->whereNotNull('organisation_stock_history_id')->distinct()->pluck('organisation_stock_history_id')
            );
        }
        $organisationStockHistoryIds = $organisationStockHistoryIds->unique()->values();

        foreach ($organisationStockHistoryIds as $organisationStockHistoryId) {
            OrganisationStockHistoryHydrateFromOrgStockHistories::run($organisationStockHistoryId);
        }
        $counts['organisation_days_rolled_up'] = $organisationStockHistoryIds->count();

        HydrateOrgStock::run($orgStock);

        return $counts;
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $orgStock = OrgStock::findOrFail($command->argument('org_stock'));
        $dryRun   = (bool)$command->option('dry-run');

        $command->info(($dryRun ? '[dry run] ' : '')."$orgStock->code $orgStock->name");
        $counts = $this->handle($orgStock, $dryRun);
        $command->table(['What', 'Value'], collect($counts)->map(fn (int|float $value, string $what) => [$what, $value])->values()->all());

        return 0;
    }
}
