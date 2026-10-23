<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 20:00:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Maintenance\Inventory\OrgStockMovement;

use App\Actions\Inventory\OrgStock\Stock\Concerns\CalculatesOrgStockHistories;
use App\Enums\Inventory\OrgStockMovement\OrgStockMovementClassEnum;
use App\Enums\Inventory\OrgStockMovement\OrgStockMovementFlowEnum;
use App\Enums\Inventory\OrgStockMovement\OrgStockMovementTypeEnum;
use App\Models\Inventory\Location;
use App\Models\Inventory\OrgStock;
use App\Models\Inventory\OrgStockMovement;
use App\Models\SysAdmin\Organisation;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * HELP-4274. Delivery note lines dispatched while picking still happened in Aurora, whose
 * Aurora pick never reached aiku, have no picked movement: the stock left the shelf with no
 * record and no cost. This writes the missing picked movement from aiku's own data, the
 * dispatched line, dated at the note's dispatch and linked to the note.
 *
 * A movement is only written when a stock check (audit or migration point) at that location
 * comes after it, so the location's current quantity, seeded from that check, does not move.
 * Lines whose Aurora pick did arrive are skipped. The rows are written with no cost; run
 * org_stock_movement:backfill_picked_cost afterwards, then the running values.
 */
class RebuildMissingPickedOrgStockMovements
{
    use AsAction;
    use CalculatesOrgStockHistories;

    public const string MARKER = 'HELP-4274';

    public function getCommandSignature(): string
    {
        return 'org_stock_movement:rebuild_missing_picks {organisation} {--from=} {--to=} {--dry-run}';
    }

    /**
     * @return array<string, array{lines: int, quantity: float, written: int, estimated_cost: float, already_picked: int, no_location: int, no_later_stock_check: int}>
     */
    public function handle(Organisation $organisation, Carbon $from, Carbon $to, bool $dryRun): array
    {
        $report = [];

        $lines = DB::table('delivery_note_items as dni')
            ->join('delivery_notes as dn', 'dn.id', 'dni.delivery_note_id')
            ->where('dn.organisation_id', $organisation->id)
            ->whereNull('dn.deleted_at')
            ->where('dn.dispatched_at', '>=', $from)
            ->where('dn.dispatched_at', '<', $to)
            ->whereNotNull('dni.org_stock_id')
            ->where('dni.quantity_dispatched', '>', 0)
            ->whereNotExists(fn ($query) => $query->select(DB::raw(1))->from('pickings')->whereColumn('pickings.delivery_note_item_id', 'dni.id'))
            ->select(['dni.id', 'dni.org_stock_id', 'dni.quantity_dispatched', 'dn.id as delivery_note_id', 'dn.dispatched_at', 'dn.source_id'])
            ->orderBy('dni.id')
            ->cursor();

        foreach ($lines as $line) {
            $date     = Carbon::parse($line->dispatched_at);
            $quantity = (float)$line->quantity_dispatched;
            $month    = $date->format('Y-m');

            $report[$month] ??= ['lines' => 0, 'quantity' => 0.0, 'written' => 0, 'estimated_cost' => 0.0, 'already_picked' => 0, 'no_location' => 0, 'no_later_stock_check' => 0];
            $report[$month]['lines']++;
            $report[$month]['quantity'] += $quantity;

            if ($this->isAlreadyPicked($line, $date, $quantity)) {
                $report[$month]['already_picked']++;
                continue;
            }

            $locationId = $this->pickingLocationId($line->org_stock_id, $date);
            if (!$locationId) {
                $report[$month]['no_location']++;
                continue;
            }

            if (!$this->hasLaterStockCheck($line->org_stock_id, $locationId, $date)) {
                $report[$month]['no_later_stock_check']++;
                continue;
            }

            $orgStock = OrgStock::find($line->org_stock_id);

            $report[$month]['written']++;
            $report[$month]['estimated_cost'] += $quantity * $this->getLppPerSku($orgStock, $date);

            if (!$dryRun) {
                $this->writeMovement($orgStock, Location::find($locationId), $line, $date, $quantity);
            }
        }

        ksort($report);

        return $report;
    }

    private function isAlreadyPicked(object $line, Carbon $date, float $quantity): bool
    {
        $linkedToNote = DB::table('org_stock_movements')
            ->where('org_stock_id', $line->org_stock_id)
            ->where('type', OrgStockMovementTypeEnum::PICKED->value)
            ->where('parent_type', 'DeliveryNote')
            ->where('parent_id', $line->delivery_note_id)
            ->where(fn ($query) => $query->whereNull('data->rebuilt')->orWhere('data->delivery_note_item_id', $line->id))
            ->exists();

        if ($linkedToNote || !$line->source_id) {
            return $linkedToNote;
        }

        return DB::table('org_stock_movements')
            ->where('org_stock_id', $line->org_stock_id)
            ->where('type', OrgStockMovementTypeEnum::PICKED->value)
            ->whereNotNull('source_id')
            ->where('quantity', -$quantity)
            ->whereBetween('date', [$date->copy()->subDays(3), $date->copy()->addDay()])
            ->exists();
    }

    private function pickingLocationId(int $orgStockId, Carbon $date): ?int
    {
        return DB::table('org_stock_movements')
            ->where('org_stock_id', $orgStockId)
            ->where('type', OrgStockMovementTypeEnum::PICKED->value)
            ->whereNotNull('location_id')
            ->where('date', '<=', $date)
            ->orderByDesc('date')
            ->value('location_id')
            ?? DB::table('location_org_stocks')
                ->where('org_stock_id', $orgStockId)
                ->orderByRaw('picking_priority nulls last')
                ->orderBy('id')
                ->value('location_id');
    }

    private function hasLaterStockCheck(int $orgStockId, int $locationId, Carbon $date): bool
    {
        return DB::table('org_stock_movements')
            ->where('org_stock_id', $orgStockId)
            ->where('location_id', $locationId)
            ->where('class', OrgStockMovementClassEnum::HELPER->value)
            ->where('date', '>', $date)
            ->exists();
    }

    private function writeMovement(OrgStock $orgStock, Location $location, object $line, Carbon $date, float $quantity): void
    {
        OrgStockMovement::create([
            'group_id'          => $location->group_id,
            'organisation_id'   => $location->organisation_id,
            'warehouse_id'      => $location->warehouse_id,
            'warehouse_area_id' => $location->warehouse_area_id,
            'location_id'       => $location->id,
            'org_stock_id'      => $orgStock->id,
            'stock_id'          => $orgStock->stock_id,
            'date'              => $date,
            'class'             => OrgStockMovementClassEnum::MOVEMENT,
            'type'              => OrgStockMovementTypeEnum::PICKED,
            'flow'              => OrgStockMovementFlowEnum::OUT,
            'is_delivered'      => true,
            'quantity'          => -$quantity,
            'org_amount'        => 0,
            'grp_amount'        => 0,
            'parent_type'       => 'DeliveryNote',
            'parent_id'         => $line->delivery_note_id,
            'data'              => ['rebuilt' => self::MARKER, 'delivery_note_item_id' => $line->id],
        ]);
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $organisation = Organisation::where('slug', $command->argument('organisation'))->first();
        if (!$organisation) {
            $command->error('Organisation not found');

            return 1;
        }

        $dryRun = (bool)$command->option('dry-run');
        $report = $this->handle(
            $organisation,
            Carbon::parse($command->option('from') ?? '2026-04-01'),
            Carbon::parse($command->option('to') ?? '2026-08-01'),
            $dryRun
        );

        $command->table(
            ['Month', 'Lines', 'Quantity', 'Written', 'Est. cost', 'Already picked', 'No location', 'No later stock check'],
            collect($report)->map(fn (array $row, string $month) => [
                $month,
                $row['lines'],
                round($row['quantity']),
                $row['written'],
                round($row['estimated_cost'], 2),
                $row['already_picked'],
                $row['no_location'],
                $row['no_later_stock_check'],
            ])->values()->all()
        );

        $command->info($dryRun ? '[dry run] nothing written' : 'Done. Next: org_stock_movement:backfill_picked_cost '.$organisation->slug.', then the running values');

        return 0;
    }
}
