<?php

namespace App\Actions\Maintenance\Catalogue;

use App\Enums\Catalogue\Product\ProductStateEnum;
use App\Enums\Catalogue\Product\ProductStatusEnum;
use App\Models\Catalogue\Product;
use Illuminate\Console\Command;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

class RepairProductsFirstAvailableAt
{
    use AsAction;

    public string $jobQueue = 'sales_slave_historic';

    /**
     * Backfills first_available_at on a product launched before the NEW IN badge existed, from its
     * first stock date or else its creation date, so it does not show up as new.
     * A product never launched (coming soon, in process) keeps it empty and gets it on its launch.
     */
    public function handle(Product $product): void
    {
        if ($product->first_available_at || $this->isNotLaunched($product)) {
            return;
        }

        $product->update(['first_available_at' => $product->first_in_stock_at ?? $product->created_at]);
    }

    private function isNotLaunched(Product $product): bool
    {
        return in_array($product->status, [ProductStatusEnum::COMING_SOON, ProductStatusEnum::IN_PROCESS])
            || $product->state === ProductStateEnum::IN_PROCESS;
    }

    public string $commandSignature = 'repair:products_first_available_at {--a|async : Dispatch each product as a job instead of repairing it here}';

    public function asCommand(Command $command): void
    {
        Nightwatch::dontSample();

        $async = (bool) $command->option('async');
        $count = 0;

        $query = Product::query()
            ->whereNull('first_available_at')
            ->whereNotIn('status', [ProductStatusEnum::COMING_SOON, ProductStatusEnum::IN_PROCESS])
            ->where('state', '!=', ProductStateEnum::IN_PROCESS);

        $progressBar = $command->getOutput()->createProgressBar($query->count());
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');
        $progressBar->start();

        $query->chunkById(5000, function ($products) use ($async, &$count, $progressBar) {
            foreach ($products as $product) {
                if ($async) {
                    self::dispatch($product);
                } else {
                    $this->handle($product);
                }
                $count++;
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $command->newLine();
        $command->info($async ? "{$count} jobs dispatched to Horizon queue {$this->jobQueue}" : "products repaired: {$count}");
    }
}
