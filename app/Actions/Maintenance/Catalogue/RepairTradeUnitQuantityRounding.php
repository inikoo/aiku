<?php

/*
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Maintenance\Catalogue;

use App\Actions\Catalogue\Product\UpdateProduct;
use App\Actions\Masters\MasterAsset\Hydrators\MasterAssetHydrateMismatch;
use App\Actions\Masters\MasterAsset\UpdateMasterAsset;
use App\Models\Catalogue\Product;
use App\Models\Masters\MasterAsset;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

/**
 * PRE-03: trade unit quantities left at 0.99999996 by the Aurora 1/12 × 12 multiplication.
 * Only the quantity moves: a master is saved only when every child following it has the same
 * composition, so the cascade cannot rewrite a child built differently, and anything whose
 * units or unit label would also change is listed and left for a person. The save clears mismatch_detected,
 * so it is recomputed from the master's anomalies straight after.
 */
class RepairTradeUnitQuantityRounding
{
    use AsAction;

    public string $commandSignature = 'repair:trade_unit_quantity_rounding {--commit} {--limit=}';

    private const string DRIFT = 'model_has_trade_units.quantity <> round(model_has_trade_units.quantity)
        and abs(model_has_trade_units.quantity - round(model_has_trade_units.quantity)) < 0.000001
        and round(model_has_trade_units.quantity) <> 0';

    private bool $commit = false;

    private ?Command $command = null;

    /** @var array<string, int> */
    private array $totals = ['masters_fixed' => 0, 'masters_skipped' => 0, 'products_fixed' => 0, 'products_skipped' => 0];

    /**
     * @return array<string, int>
     */
    public function handle(bool $commit, ?int $limit = null, ?Command $command = null): array
    {
        $this->commit  = $commit;
        $this->command = $command;

        $this->masters($limit);
        $this->products($limit);

        $command?->info(collect($this->totals)->map(fn (int $count, string $key) => "$key: $count")->implode(', '));
        if (!$commit) {
            $command?->warn('Dry run, pass --commit to write');
        }

        return $this->totals;
    }

    private function masters(?int $limit): void
    {
        $query = MasterAsset::whereExists($this->drift('MasterAsset', 'master_assets.id'))->with(['tradeUnits', 'masterShop'])->orderBy('id');
        if ($limit) {
            $query->limit($limit);
        }

        foreach ($query->get() as $masterAsset) {
            $before = $this->composition($masterAsset->tradeUnits);
            $after  = $before->map(fn ($quantity) => wholeTradeUnitQuantity($quantity));
            $label  = "MASTER {$masterAsset->masterShop->slug} $masterAsset->code";

            $children = $masterAsset->products()->where('products.not_follow_master_trade_units', false)->with('tradeUnits')->get();

            $differentChildren = $children->filter(
                fn (Product $product) => $this->composition($product->tradeUnits)->map(fn ($quantity) => (float)wholeTradeUnitQuantity($quantity))->sortKeys()->all()
                    != $after->map(fn ($quantity) => (float)$quantity)->sortKeys()->all()
            );

            $updateMasterAsset = UpdateMasterAsset::make();
            $unitsAfter        = $updateMasterAsset->getUnitsFromTradeUnits($this->tradeUnitsData($after));

            $reason = match (true) {
                $differentChildren->isNotEmpty() => 'children built differently: '.$differentChildren->map(fn (Product $product) => $product->shop->slug.' '.$product->code)->implode(', '),
                !$masterAsset->has_independent_units && $unitsAfter['units'] !== null && (float)$unitsAfter['units'] != (float)$masterAsset->units => "units would change $masterAsset->units → {$unitsAfter['units']}",
                $masterAsset->unit === null && $unitsAfter['unit'] !== null => "unit label would be set to {$unitsAfter['unit']}",
                default => null,
            };

            $this->write(
                $label,
                $masterAsset->tradeUnits->pluck('code', 'id'),
                $before,
                $after,
                $reason,
                $children->count().' children',
                function () use ($updateMasterAsset, $masterAsset, $after) {
                    $masterAsset = $updateMasterAsset->action($masterAsset, array_filter([
                        'trade_units' => $this->tradeUnitsData($after),
                        'unit'        => $masterAsset->unit,
                    ], fn ($value) => $value !== null));
                    MasterAssetHydrateMismatch::run($masterAsset);
                },
                'masters'
            );
        }
    }

    private function products(?int $limit): void
    {
        $query = Product::whereExists($this->drift('Product', 'products.id'))
            ->where(fn (Builder $query) => $query
                ->whereNull('products.master_product_id')
                ->orWhere('products.not_follow_master_trade_units', true)
                ->orWhereNotExists($this->drift('MasterAsset', 'products.master_product_id')))
            ->with(['tradeUnits', 'shop'])
            ->orderBy('id');
        if ($limit) {
            $query->limit($limit);
        }

        foreach ($query->get() as $product) {
            $before = $this->composition($product->tradeUnits);
            $after  = $before->map(fn ($quantity) => wholeTradeUnitQuantity($quantity));

            $unitLabelAfter = UpdateProduct::make()->getUnitsFromTradeUnits($this->tradeUnitsData($after))['unit'];

            $this->write(
                "PRODUCT {$product->shop->slug} $product->code",
                $product->tradeUnits->pluck('code', 'id'),
                $before,
                $after,
                $product->unit === null && $unitLabelAfter !== null ? "unit label would be set to $unitLabelAfter" : null,
                $product->state->value,
                fn () => UpdateProduct::make()->action($product, array_filter([
                    'trade_units' => $this->tradeUnitsData($after),
                    'units'       => $product->units,
                    'unit'        => $product->unit,
                ], fn ($value) => $value !== null)),
                'products'
            );
        }
    }

    private function write(string $label, Collection $codes, Collection $before, Collection $after, ?string $reason, string $note, callable $save, string $kind): void
    {
        $changes = $before->map(fn ($quantity, $tradeUnitId) => $codes[$tradeUnitId].' '.($quantity == $after[$tradeUnitId] ? $quantity : "$quantity → {$after[$tradeUnitId]}"))->implode(', ');

        if ($reason) {
            $this->totals[$kind.'_skipped']++;
            $this->command?->line("SKIP $label: $changes ($reason)");

            return;
        }

        if ($this->commit) {
            try {
                $save();
            } catch (Throwable $exception) {
                $this->totals[$kind.'_skipped']++;
                $this->command?->error("FAIL $label: $changes ({$exception->getMessage()})");

                return;
            }
        }

        $this->totals[$kind.'_fixed']++;
        $this->command?->line(($this->commit ? 'FIXED' : 'WOULD FIX')." $label: $changes ($note)");
    }

    private function drift(string $modelType, string $modelIdColumn): \Closure
    {
        return fn ($query) => $query->select(DB::raw(1))
            ->from('model_has_trade_units')
            ->where('model_has_trade_units.model_type', $modelType)
            ->whereColumn('model_has_trade_units.model_id', $modelIdColumn)
            ->whereRaw(self::DRIFT);
    }

    /**
     * @return Collection<int, string>
     */
    private function composition(Collection $tradeUnits): Collection
    {
        return $tradeUnits->mapWithKeys(fn ($tradeUnit) => [$tradeUnit->id => $tradeUnit->pivot->quantity]);
    }

    /**
     * @return list<array{id: int, quantity: int|float|string}>
     */
    private function tradeUnitsData(Collection $quantities): array
    {
        return $quantities->map(fn ($quantity, $tradeUnitId) => ['id' => $tradeUnitId, 'quantity' => $quantity])->values()->all();
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();
        ini_set('memory_limit', '2G');
        DB::connection()->disableQueryLog();

        $limit = $command->option('limit');

        $this->handle((bool)$command->option('commit'), $limit ? (int)$limit : null, $command);

        return 0;
    }
}
