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
        $transactions = $recurringBill->transactions;

        $today = Carbon::now()->startOfDay();
    
        foreach ($transactions as $transaction) {
            $startDate = Carbon::parse($transaction->start_date)->startOfDay();
        
            if ($startDate->equalTo($today)) {
                $daysDifference = 1;
            } else {
                $daysDifference = abs($today->diffInDays($startDate));
            }

            $assetPrice = $transaction->asset->price;
            $transaction->update([
                'quantity' => $daysDifference, 
                'net_amount' => $daysDifference * $assetPrice,
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

