<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 06-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Actions\Catalogue\Asset\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Ordering\Transaction\TransactionStateEnum;
use App\Enums\Ordering\Transaction\TransactionStatusEnum;
use App\Models\Catalogue\Asset;
use App\Models\Ordering\Transaction;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class AssetHydrateTransactions
{
    use AsAction;
    use WithEnumStats;

    private Asset $asset;

    public function __construct(Asset $asset)
    {
        $this->asset = $asset;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->asset->id))->dontRelease()];
    }

    public function handle(Asset $asset): void
    {
        $transactions = $asset->transactions();
        $transactionsOutOfStockInBasket = $transactions->where('out_of_stock_in_basket', true)->get();

        $stats = [
            'number_transactions_out_of_stock_in_basket' => $transactionsOutOfStockInBasket->count(),
            'out_of_stock_in_basket_grp_net_amount' => $transactionsOutOfStockInBasket->sum('grp_net_amount'),
            'out_of_stock_in_basket_org_net_amount' => $transactionsOutOfStockInBasket->sum('org_net_amount'),
            'out_of_stock_in_basket_net_amount' => $transactionsOutOfStockInBasket->sum('net_amount'),
            'number_transactions' => $transactions->count(),
            'number_current_transactions' => $transactions->where('state', '!=', TransactionStateEnum::CANCELLED)->count(),
        ];

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'transactions',
                field: 'state',
                enum: TransactionStateEnum::class,
                models: Transaction::class,
                where: function ($q) use ($transactions) {
                    $q->whereIn('id', $transactions->pluck('id'));
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
                where: function ($q) use ($transactions) {
                    $q->whereIn('id', $transactions->pluck('id'));
                }
            )
        );

        $asset->orderingStats()->update($stats);
    }
}
