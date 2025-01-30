<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 Jan 2025 21:09:15 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2025, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBillTransaction;

use App\Actions\OrgAction;
use App\Enums\Billables\Rental\RentalUnitEnum;
use App\Models\Fulfilment\RecurringBillTransaction;
use App\Models\Fulfilment\Space;
use Carbon\Carbon;

class CalculateRecurringBillTransactionTemporalQuantity extends OrgAction
{
    public function handle(RecurringBillTransaction $recurringBillTransaction): RecurringBillTransaction
    {
        if (!in_array($recurringBillTransaction->item_type, ['Pallet', 'StoredItem', 'Space'])) {
            return $recurringBillTransaction;
        }

        $onlyWorkDays = false;
        if ($recurringBillTransaction->item_type == 'Space') {
            $space = Space::find($recurringBillTransaction->item_id);
            if ($space && $space->exclude_weekend) {
                $onlyWorkDays = true;
            }
        }


        $temporalQuantity = 1;

        $today   = Carbon::now()->setTimezone('UTC')->startOfDay();
        $endDate = $today;
        if ($recurringBillTransaction->end_date) {
            if (Carbon::parse($recurringBillTransaction->end_date)->setTimezone('UTC')->startOfDay()->isBefore($today)) {
                $endDate = $recurringBillTransaction->end_date;
            }
        }


        $startDate = Carbon::parse($recurringBillTransaction->start_date)->setTimezone('UTC')->startOfDay();


        if ($recurringBillTransaction->asset->rental->unit == RentalUnitEnum::DAY) {
            if ($onlyWorkDays) {
                $temporalQuantity = $this->getWorkDays($startDate, $endDate);
            } else {
                $temporalQuantity = $this->getDays($startDate, $endDate);
            }
        } elseif ($recurringBillTransaction->asset->rental->unit == RentalUnitEnum::MONTH) {
            $temporalQuantity = $this->getMonths($startDate, $endDate);
        }


        $recurringBillTransaction->update([
            'temporal_quantity' => $temporalQuantity,
        ]);

        return $recurringBillTransaction;
    }


    public function getMonths($startDate, $endDate): int
    {
        $start = Carbon::parse($startDate)->startOfMonth();
        $end   = Carbon::parse($endDate)->endOfMonth();

        return $start->diffInMonths($end) + 1;
    }

    public function getWorkDays($startDate, $endDate): int
    {
        return floor($startDate->diffInWeekdays($endDate) + 1);
    }

    public function getDays($startDate, $endDate): int
    {
        return floor($startDate->diffInDays($endDate) + 1);
    }


    public function action(RecurringBillTransaction $recurringBillTransaction, int $hydratorsDelay = 0): RecurringBillTransaction
    {
        $this->asAction       = true;
        $this->hydratorsDelay = $hydratorsDelay;
        $this->initialisationFromFulfilment($recurringBillTransaction->fulfilment, []);

        return $this->handle($recurringBillTransaction);
    }

}
