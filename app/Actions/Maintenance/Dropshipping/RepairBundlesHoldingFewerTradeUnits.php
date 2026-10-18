<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 15:30:00 Central European Summer Time, Trnava, Slovakia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Maintenance\Dropshipping;

use App\Actions\Dropshipping\Bundle\UpdateBundle;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Shop;
use App\Models\Dropshipping\Bundle;
use App\Models\Goods\TradeUnit;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * Gives a bundle the trade units its own items add up to, when it holds fewer of them.
 *
 * Bundles created before a component made of several trade units was multiplied by that quantity
 * carry one piece where the customer chose, and paid for, a whole pack, so the warehouse picks one.
 *
 * Only the trade units of the bundle product are written. Its price, its units and the sku of its
 * portfolio stay as they are, and nothing is pushed to the store of the customer: the sku is already
 * on their listings. A bundle that holds more than its items say, or other trade units than its
 * items, is reported and left for a person to look at, who can then give that one bundle exactly
 * what its items add up to with --rederive.
 */
class RepairBundlesHoldingFewerTradeUnits
{
    use AsAction;

    public const string REPAIRED     = 'repaired';
    public const string CORRECT      = 'correct';
    public const string NEEDS_REVIEW = 'needs_review';

    private const float TOLERANCE = 0.01;

    public string $commandSignature = 'repair:bundles_holding_fewer_trade_units {shop? : Slug of one shop, all shops when omitted} {--bundle= : Code of one bundle product} {--rederive : With --bundle, give the bundle exactly what its items add up to, even when it holds more or other trade units} {--dry-run : Report what would be repaired without writing}';

    /**
     * @return array{outcome: string, changes: array<int, array{trade_unit: string, from: float, to: float}>, reason?: string}
     */
    public function handle(Bundle $bundle, bool $dryRun = false, bool $rederive = false): array
    {
        /** @var Product $product */
        $product = $bundle->bundleable;

        $expected = collect(UpdateBundle::make()->getCurrentBundleTradeUnits($bundle))
            ->mapWithKeys(fn (array $tradeUnit) => [$tradeUnit['id'] => (float) $tradeUnit['quantity']]);

        $current = $product->tradeUnits()->get()->mapWithKeys(fn ($tradeUnit) => [$tradeUnit->id => (float) $tradeUnit->pivot->quantity]);

        if ($expected->isEmpty()) {
            return ['outcome' => self::NEEDS_REVIEW, 'changes' => [], 'reason' => 'its items have no trade units'];
        }

        if (!$rederive && $expected->keys()->sort()->values()->all() !== $current->keys()->sort()->values()->all()) {
            return ['outcome' => self::NEEDS_REVIEW, 'changes' => [], 'reason' => 'trade units are not those of its items'];
        }

        $codes   = TradeUnit::whereIn('id', $expected->keys()->merge($current->keys()))->pluck('code', 'id');
        $changes = [];
        foreach ($expected->keys()->merge($current->keys())->unique() as $tradeUnitId) {
            $held     = $current->get($tradeUnitId, 0.0);
            $quantity = $expected->get($tradeUnitId, 0.0);

            if (!$rederive && $held - $quantity > self::TOLERANCE) {
                return ['outcome' => self::NEEDS_REVIEW, 'changes' => [], 'reason' => 'holds more than its items say'];
            }

            if (abs($quantity - $held) > self::TOLERANCE) {
                $changes[] = ['trade_unit' => $codes[$tradeUnitId], 'from' => $held, 'to' => $quantity];
            }
        }

        if (!$changes) {
            return ['outcome' => self::CORRECT, 'changes' => []];
        }

        if (!$dryRun) {
            try {
                UpdateBundle::make()->writeCurrentBundleTradeUnits($bundle);
            } catch (ValidationException $exception) {
                return ['outcome' => self::NEEDS_REVIEW, 'changes' => [], 'reason' => collect($exception->errors())->flatten()->first()];
            }
        }

        return ['outcome' => self::REPAIRED, 'changes' => $changes];
    }

    public function bundlesQuery(?Shop $shop = null, ?string $bundleCode = null): Builder
    {
        return Bundle::whereHasMorph(
            'bundleable',
            [Product::class],
            fn (Builder $query) => $query
                ->when($shop, fn (Builder $query) => $query->where('shop_id', $shop->id))
                ->when($bundleCode, fn (Builder $query) => $query->where('code', $bundleCode))
        );
    }

    public function asCommand(Command $command): int
    {
        Nightwatch::dontSample();

        $shop = null;
        if ($command->argument('shop')) {
            $shop = Shop::where('slug', $command->argument('shop'))->first();
            if (!$shop) {
                $command->error('Shop not found');

                return 1;
            }
        }

        $rederive = (bool) $command->option('rederive');
        if ($rederive && !$command->option('bundle')) {
            $command->error('--rederive works on one bundle at a time, name it with --bundle');

            return 1;
        }

        $dryRun   = (bool) $command->option('dry-run');
        $query    = $this->bundlesQuery($shop, $command->option('bundle'));
        $counts   = [self::REPAIRED => 0, self::CORRECT => 0, self::NEEDS_REVIEW => 0];
        $repaired = [];
        $review   = [];

        $progressBar = $command->getOutput()->createProgressBar($query->count());

        $query->with('bundleable')->chunkById(200, function ($bundles) use (&$counts, &$repaired, &$review, $dryRun, $rederive, $progressBar) {
            foreach ($bundles as $bundle) {
                $result = $this->handle($bundle, $dryRun, $rederive);
                $counts[$result['outcome']]++;

                foreach ($result['changes'] as $change) {
                    $repaired[] = [$bundle->bundleable->code, $change['trade_unit'], $change['from'], $change['to']];
                }

                if ($result['outcome'] === self::NEEDS_REVIEW) {
                    $review[] = [$bundle->bundleable->code, $result['reason']];
                }

                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $command->newLine(2);

        $command->table(
            [$dryRun ? 'Would repair' : 'Repaired', 'Already correct', 'Left alone, needs review'],
            [array_values($counts)]
        );

        if ($repaired) {
            $command->table(['Bundle', 'Trade unit', 'Held', $dryRun ? 'Would hold' : 'Now holds'], $repaired);
        }

        if ($review) {
            $command->table(['Bundle', 'Why it was left alone'], $review);
        }

        return 0;
    }
}
