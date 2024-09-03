<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 01 Jun 2024 19:53:53 Central European Summer Time, Mijas Costa, Spain
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Ordering\Order\Hydrators;

use App\Actions\Traits\WithEnumStats;

use App\Enums\Ordering\Order\OrderStateEnum;
use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Models\Ordering\Order;
use App\Models\Ordering\Transaction;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class OrderHydrateTransactions
{
    use AsAction;
    use WithEnumStats;
    private Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->order->id))->dontRelease()];
    }
    public function handle(Order $order): void
    {

        $stats= [
            'number_transactions' => $order->transactions()->count(),
        ];

        if($order->state == OrderStateEnum::CREATING) {
            $stats['number_transactions_at_creation' ]= $order->transactions()->count();

        }


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'transactions',
                field: 'state',
                enum: TransactionStateEnum::class,
                models: Transaction::class,
                where: function ($q) use ($order) {
                    $q->where('order_id', $order->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'transactions',
                field: 'status',
                enum: TransactionStatusEnum::class,
                models: Transaction::class,
                where: function ($q) use ($order) {
                    $q->where('order_id', $order->id);
                }
            )
        );



        $stats['number_current_transactions']=$stats['number_transactions']-$stats['number_transactions_state_cancelled'];

        $order->stats()->update($stats);
    }

}
