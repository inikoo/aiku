<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 12 Oct 2026 15:30:00 Central European Summer Time, Trnava, Slovakia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Maintenance\Dropshipping;

use App\Actions\Catalogue\Product\UpdateProduct;
use App\Actions\Dropshipping\Bundle\UpdateBundle;
use App\Models\Catalogue\Product;
use App\Models\Catalogue\Shop;
use App\Models\Dropshipping\Bundle;
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
 * items, is reported and left for a person to look at.
 */
class RepairBundlesHoldingFewerTradeUnits
{
    use AsAction;

    public const string REPAIRED     = 'repaired';
    public const string CORRECT      = 'correct';
    public const string NEEDS_REVIEW = 'needs_review';

    private const float TOLERANCE = 0.01;

    public string $commandSignature = 'repair:bundles_holding_fewer_trade_units {shop? : Slug of one shop, all shops when omitted} {--bundle= : Code of one bundle product} {--dry-run : Report what would be repaired without writing}';

    /**
     * @return array{outcome: string, changes: array<int, array{trade_unit: string, from: float, to: float}>, reason?: string}
     */
    public function handle(Bundle $bundle, bool $dryRun = false): array
    {
        /** @var Product $product */
        $product = $bundle->bundleable;

        $expected = collect(UpdateBundle::make()->getCurrentBundleTradeUnits($bundle))
            ->mapWithKeys(fn (array $tradeUnit) => [$tradeUnit['id'] => (float) $tradeUnit['quantity']]);

        $tradeUnits = $product->tradeUnits()->get();
        $current    = $tradeUnits->mapWithKeys(fn ($tradeUnit) => [$tradeUnit->id => (float) $tradeUnit->pivot->quantity]);

        if ($expected->isEmpty() || $expected->keys()->sort()->values()->all() !== $current->keys()->sort()->values()->all()) {
            return ['outcome' => self::NEEDS_REVIEW, 'changes' => [], 'reason' => 'trade units are not those of its items'];
        }

        $changes = [];
        foreach ($expected as $tradeUnitId => $quantity) {
            if ($current[$tradeUnitId] - $quantity > self::TOLERANCE) {
                return ['outcome' => self::NEEDS_REVIEW, 'changes' => [], 'reason' => 'holds more than its items say'];
            }

            if ($quantity - $current[$tradeUnitId] > self::TOLERANCE) {
                $changes[] = [
                    'trade_unit' => $tradeUnits->firstWhere('id', $tradeUnitId)->code,
                    'from'       => $current[$tradeUnitId],
                    'to'         => $quantity,
                ];
            }
        }

        if (!$changes) {
            return ['outcome' => self::CORRECT, 'changes' => []];
        }

        if (!$dryRun) {
            try {
                UpdateProduct::make()->action($product, [
                    'trade_units' => $expected->map(fn (float $quantity, int $tradeUnitId) => ['id' => $tradeUnitId, 'quantity' => $quantity])->values()->all(),
                    'units'       => $product->units,
                ]);
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

        $dryRun   = (bool) $command->option('dry-run');
        $query    = $this->bundlesQuery($shop, $command->option('bundle'));
        $counts   = [self::REPAIRED => 0, self::CORRECT => 0, self::NEEDS_REVIEW => 0];
        $repaired = [];
        $review   = [];

        $progressBar = $command->getOutput()->createProgressBar($query->count());

        $query->with('bundleable')->chunkById(200, function ($bundles) use (&$counts, &$repaired, &$review, $dryRun, $progressBar) {
            foreach ($bundles as $bundle) {
                $result = $this->handle($bundle, $dryRun);
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
