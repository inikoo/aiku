<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 24 Oct 2026 02:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Maintenance\Inventory\OrgStockMovement;

use App\Actions\Helpers\CurrencyExchange\GetHistoricCurrencyExchange;
use App\Actions\Inventory\OrganisationStockHistory\Hydrators\OrganisationStockHistoryHydrateFromOrgStockHistories;
use App\Actions\Inventory\OrgStockMovement\CalculateOrgStockMovementRunningValues;
use App\Actions\Traits\WithStockHistoryArchiveWrite;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * HELP-4274. Stock counts fetched from Aurora (found and write-off) carry Aurora's amount, worked
 * out from its running cost per SKO, which swings: a label sheet bought at £0.47 was written off
 * at £294. Every organisation now counts stock in aiku, so only that history is wrong. A count
 * whose price is more than three times, or less than a third of, the SKU's median purchase price
 * is priced at that median instead; the old rows are copied to
 * help4274_recosted_org_stock_movements first. Counts with no purchase to compare against, or
 * whose purchases disagree by more than ten times among themselves (a box priced as a sheet),
 * are left alone: there is no cost to trust. With --picks the same is done for Aurora picks and
 * their returns, but only when priced more than ten times the median (a pipette bought at £0.05
 * picked at £569.70): a pick a few times off can be a real older or landed cost, and one below the
 * median an older, cheaper purchase. The SKUs' running values, daily
 * histories and organisation stock value days are then worked out again.
 */
class RecostAuroraStockCountMovements
{
    use AsAction;
    use WithStockHistoryArchiveWrite;

    private const string BACKUP_TABLE = 'help4274_recosted_org_stock_movements';

    private const int TOLERANCE = 3;

    private const int PICK_TOLERANCE = 10;

    private const int PURCHASE_SPREAD = 10;

    private const array COUNT_TYPES = ['found', 'write-off'];

    private const array PICK_TYPES = ['picked', 'return-picked', 'cancel-picked'];

    /** @var array<string, float> */
    private array $monthlyRates = [];

    public function getCommandSignature(): string
    {
        return 'org_stock_movement:recost_aurora_counts {organisation} {--from=2024-01-01} {--picks : picks and their returns priced more than ten times too high, instead of counts} {--org-stock=* : only these SKO codes} {--dry-run}';
    }

    /**
     * @return Collection<int, object{id: int, org_stock_id: int, code: string, type: string, date: string, quantity: float, org_amount: float, purchase_cost: float}>
     */
    public function mispricedCounts(Organisation $organisation, string $from, bool $picks = false, array $orgStockCodes = []): Collection
    {
        return collect(DB::select(
            "select * from (
                select m.id, m.org_stock_id, os.code, m.type, m.date, m.quantity::float as quantity, m.org_amount::float as org_amount,
                    purchases.median_cost::float as purchase_cost
                from org_stock_movements m
                join org_stocks os on os.id = m.org_stock_id
                join lateral (
                    select percentile_cont(0.5) within group (order by coalesce(p.cost_per_sku, p.org_amount / p.quantity)) as median_cost,
                        min(coalesce(p.cost_per_sku, p.org_amount / p.quantity)) as lowest,
                        max(coalesce(p.cost_per_sku, p.org_amount / p.quantity)) as highest
                    from org_stock_movements p
                    where p.org_stock_id = m.org_stock_id and p.type = 'purchase' and p.quantity > 0
                    and coalesce(p.cost_per_sku, p.org_amount / p.quantity) > 0
                ) purchases on purchases.highest <= purchases.lowest * ?
                where m.organisation_id = ? and m.source_id is not null and m.type = any(?::text[]) and (cardinality(?::text[]) = 0 or os.code = any(?::text[])) and m.quantity <> 0 and m.date >= ?
            ) counts
            where purchase_cost > 0 and (abs(org_amount / quantity) > purchase_cost * ? or (? = 0 and abs(org_amount / quantity) < purchase_cost / ?))
            order by abs(org_amount - quantity * purchase_cost) desc",
            [
                self::PURCHASE_SPREAD,
                $organisation->id,
                '{'.implode(',', $picks ? self::PICK_TYPES : self::COUNT_TYPES).'}',
                $this->textArray($orgStockCodes),
                $this->textArray($orgStockCodes),
                $from,
                $picks ? self::PICK_TOLERANCE : self::TOLERANCE,
                (int)$picks,
                self::TOLERANCE,
            ]
        ));
    }

    /**
     * @return array{movements: int, recorded: float, recosted: float, org_stocks: int, organisation_days_rolled_up: int}
     */
    public function handle(Organisation $organisation, string $from, bool $dryRun, bool $picks = false, array $orgStockCodes = []): array
    {
        $counts = $this->mispricedCounts($organisation, $from, $picks, $orgStockCodes);

        $report = [
            'movements'                   => $counts->count(),
            'recorded'                    => round($counts->sum('org_amount'), 2),
            'recosted'                    => round($counts->sum(fn ($count) => $count->quantity * $count->purchase_cost), 2),
            'org_stocks'                  => $counts->pluck('org_stock_id')->unique()->count(),
            'organisation_days_rolled_up' => 0,
        ];

        if ($dryRun || $counts->isEmpty()) {
            return $report;
        }

        DB::transaction(function () use ($counts, $organisation) {
            DB::statement('create table if not exists '.self::BACKUP_TABLE.' (like org_stock_movements)');
            DB::statement(
                'insert into '.self::BACKUP_TABLE.' select * from org_stock_movements where id = any(?::bigint[]) and id not in (select id from '.self::BACKUP_TABLE.')',
                ['{'.$counts->pluck('id')->implode(',').'}']
            );

            foreach ($counts as $count) {
                $orgAmount = round($count->quantity * $count->purchase_cost, 3);
                DB::table('org_stock_movements')->where('id', $count->id)->update([
                    'org_amount'   => $orgAmount,
                    'grp_amount'   => round($orgAmount * $this->rateForMonth($organisation, Carbon::parse($count->date)), 3),
                    'cost_per_sku' => $count->purchase_cost,
                ]);
            }
        });

        $organisationStockHistoryIds = collect();
        foreach ($counts->groupBy('org_stock_id') as $orgStockId => $orgStockCounts) {
            CalculateOrgStockMovementRunningValues::run($orgStockId);
            RecalculateOrgStockHistoriesPostCostFix::run($orgStockId, true);

            $from = Carbon::parse($orgStockCounts->min('date'))->format('Y-m-d');
            foreach ($this->stockHistoryWriteConnections() as $connection) {
                $organisationStockHistoryIds = $organisationStockHistoryIds->concat(
                    DB::connection($connection)->table('org_stock_histories')
                        ->where('org_stock_id', $orgStockId)
                        ->where('date', '>=', $from)
                        ->whereNotNull('organisation_stock_history_id')
                        ->distinct()
                        ->pluck('organisation_stock_history_id')
                );
            }
        }

        $organisationStockHistoryIds = $organisationStockHistoryIds->unique()->values();
        foreach ($organisationStockHistoryIds as $organisationStockHistoryId) {
            OrganisationStockHistoryHydrateFromOrgStockHistories::run($organisationStockHistoryId);
        }
        $report['organisation_days_rolled_up'] = $organisationStockHistoryIds->count();

        return $report;
    }

    /**
     * @param array<int, string> $values
     */
    private function textArray(array $values): string
    {
        return '{'.implode(',', array_map(fn (string $value) => '"'.str_replace(['\\', '"'], ['\\\\', '\\"'], $value).'"', $values)).'}';
    }

    private function rateForMonth(Organisation $organisation, Carbon $date): float
    {
        return $this->monthlyRates[$date->format('Y-m')] ??= GetHistoricCurrencyExchange::run(
            $organisation->currency,
            $organisation->group->currency,
            $date
        ) ?? 1.0;
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $organisation = Organisation::where('slug', $command->argument('organisation'))->firstOrFail();
        $dryRun       = (bool)$command->option('dry-run');
        $from         = (string)$command->option('from');
        $picks        = (bool)$command->option('picks');
        $orgStockCodes = $command->option('org-stock');

        if ($dryRun) {
            $command->table(
                ['Movement', 'SKO', 'Type', 'Date', 'Quantity', 'Recorded', 'Per SKO', 'Purchase cost', 'Recosted'],
                $this->mispricedCounts($organisation, $from, $picks, $orgStockCodes)->map(fn ($count) => [
                    $count->id,
                    $count->code,
                    $count->type,
                    substr($count->date, 0, 10),
                    $count->quantity,
                    round($count->org_amount, 2),
                    round($count->org_amount / $count->quantity, 2),
                    round($count->purchase_cost, 4),
                    round($count->quantity * $count->purchase_cost, 2),
                ])->all()
            );
        }

        $report = $this->handle($organisation, $from, $dryRun, $picks, $orgStockCodes);
        $command->table(['What', 'Value'], collect($report)->map(fn (int|float $value, string $what) => [$what, $value])->values()->all());
        $command->info($dryRun ? '[dry run] nothing written' : 'Done');

        return 0;
    }
}
