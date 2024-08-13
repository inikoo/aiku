<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Apr 2024 09:00:21 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBillTransaction\Hydrators;

use App\Models\Fulfilment\RecurringBill;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Lorisleiva\Actions\Concerns\AsCommand;

class HydrateRecurringBillTransactionRentalQuantity
{
    use AsCommand;

    public string $commandSignature = 'recurring-bill:update-quantity {recurringBill}';

    public function handle(RecurringBill $recurringBill)
    {
        $transactions = $recurringBill->transactions()->where('item_type', 'Pallet')->get();

        $today = Carbon::now()->startOfDay();
        $todayString = $today->toDateString(); 

        foreach ($transactions as $transaction) {
            $startDate = Carbon::parse($transaction->start_date)->startOfDay();
            $startDateString = $startDate->toDateString();

            $daysDifference = $startDate->diffInDays($today);

            if ($startDateString === $todayString) {
                $daysDifference = 1;
            } elseif ($startDateString === $today->copy()->subDay()->toDateString()) {
                $daysDifference = 2;
            }

            $assetPrice = $transaction->asset->price;
            $transaction->update([
                'quantity'     => $daysDifference,
                'net_amount'   => $daysDifference * $assetPrice,
                'gross_amount' => $daysDifference * $assetPrice
            ]);
    }

    return $recurringBill;
    }

    public function asCommand(Command $command): int
    {
        $recurringBill = RecurringBill::where('reference', $command->argument('recurringBill'))->firstOrFail();

        $this->handle($recurringBill);

        echo "Recurring Bill Transaction Updated: $recurringBill->reference\n";

        return 0;
    }
}
