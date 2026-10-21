<?php

/*
 * Author: Vika Aqordi
 * Created on: 2026-10-21 09:30
 * Github: https://github.com/aqordeon
 * Copyright: 2026
 */

namespace App\Actions\Maintenance\Goods;

use App\Actions\Goods\Stock\UpdateStock;
use App\Actions\Goods\TradeUnit\UpdateTradeUnit;
use App\Actions\Inventory\OrgStock\UpdateOrgStock;
use App\Models\Goods\Stock;
use App\Models\Goods\TradeUnit;
use App\Models\Inventory\OrgStock;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Aurora parts whose name was pasted from a broken spreadsheet formula came across named
 * "#REF!" (or "#N/A", "#VALUE!"...), and that name is printed on packing instructions and
 * labels. Aurora no longer rewrites a stock, SKO or trade unit aiku already has, so each one
 * takes the name of a linked record that has a real one: the supplier product first, as
 * that is what the supplier sells it as, then the stock or trade unit beside it.
 */
class RepairSpreadsheetErrorNames
{
    use AsAction;

    public const string SPREADSHEET_ERROR_PATTERN = '^\s*#(REF!|N/A|VALUE!|NAME\?|DIV/0!|NULL!|NUM!)';

    public string $commandSignature = 'repair:spreadsheet_error_names {--commit}';

    public function handle(bool $commit, ?Command $command = null): int
    {
        $repaired = 0;

        foreach ($this->withSpreadsheetErrorName(Stock::query())->get() as $stock) {
            $name = $this->firstRealName(
                $stock->supplierProducts()->pluck('supplier_products.name'),
                $stock->tradeUnits()->pluck('trade_units.name')
            );

            $repaired += $this->repair($stock, $name, $commit, $command, fn () => UpdateStock::make()->action($stock, ['name' => $name], strict: false));
        }

        foreach ($this->withSpreadsheetErrorName(OrgStock::query())->with('stock')->get() as $orgStock) {
            $name = $this->firstRealName(
                collect([$orgStock->stock?->name]),
                $orgStock->stock?->supplierProducts()->pluck('supplier_products.name') ?? collect(),
                $orgStock->stock?->tradeUnits()->pluck('trade_units.name') ?? collect()
            );

            $repaired += $this->repair($orgStock, $name, $commit, $command, fn () => UpdateOrgStock::make()->action($orgStock, ['name' => $name], strict: false));
        }

        foreach ($this->withSpreadsheetErrorName(TradeUnit::query())->get() as $tradeUnit) {
            $name = $this->firstRealName(
                $tradeUnit->supplierProducts()->pluck('supplier_products.name'),
                $tradeUnit->stocks()->pluck('stocks.name')
            );

            $repaired += $this->repair($tradeUnit, $name, $commit, $command, fn () => UpdateTradeUnit::make()->action($tradeUnit, ['name' => $name], strict: false));
        }

        if (!$commit) {
            $command?->warn('Dry run, pass --commit to write');
        }

        return $repaired;
    }

    public static function isSpreadsheetError(?string $value): bool
    {
        return $value !== null && preg_match('~'.self::SPREADSHEET_ERROR_PATTERN.'~', $value) === 1;
    }

    protected function withSpreadsheetErrorName(Builder $query): Builder
    {
        return $query->whereRaw($query->getModel()->getTable().'.name COLLATE "C" ~ ?', [self::SPREADSHEET_ERROR_PATTERN]);
    }

    protected function firstRealName(Collection ...$candidates): ?string
    {
        return collect($candidates)
            ->flatten()
            ->map(fn (?string $name) => trim((string) $name))
            ->first(fn (string $name) => $name !== '' && !self::isSpreadsheetError($name));
    }

    protected function repair(Stock|OrgStock|TradeUnit $model, ?string $name, bool $commit, ?Command $command, callable $update): int
    {
        $label = class_basename($model).' '.$model->code.' (id '.$model->id.')';

        if ($name === null) {
            $command?->error("$label: no linked record has a real name, fix it by hand");

            return 0;
        }

        $command?->line("$label: $model->name → $name");

        if ($commit) {
            $update();
        }

        return 1;
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $repaired = $this->handle((bool)$command->option('commit'), $command);
        $command->info("$repaired names ".($command->option('commit') ? 'repaired' : 'to repair'));

        return 0;
    }
}
