<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 20 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Actions\Maintenance\Ordering;

use App\Actions\Ordering\Order\UpdateState\CancelOrder;
use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Models\Ordering\Order;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Nightwatch\Facades\Nightwatch;
use Lorisleiva\Actions\Concerns\AsAction;

class RepairCancelledOrdersLiveTransactions
{
    use AsAction;

    public string $commandSignature = 'repair:cancelled_orders_live_transactions';

    public function handle(Order $order): void
    {
        CancelOrder::make()->cancelTransactions($order, $order->cancelled_at ?? $order->updated_at);
    }

    public function asCommand(Command $command): void
    {
        Nightwatch::dontSample();

        $query = Order::where('state', OrderStateEnum::CANCELLED)
            ->whereHas('transactions', fn (Builder $q) => $q->whereNotIn('state', [TransactionStateEnum::CANCELLED, TransactionStateEnum::DISPATCHED]));

        $bar = $command->getOutput()->createProgressBar($query->count());
        $bar->setFormat('debug');
        $bar->start();

        $query->chunkById(500, function ($orders) use ($bar) {
            foreach ($orders as $order) {
                $this->handle($order);
                $bar->advance();
            }
        });

        $bar->finish();
        $command->newLine();
    }
}
