<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 28 Apr 2024 13:00:30 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\RecurringBill\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Models\Fulfilment\RecurringBill;
use App\Models\Fulfilment\RecurringBillTransaction;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class RecurringBillHydratePallets
{
    use AsAction;
    use WithEnumStats;

    private RecurringBill $recurringBill;

    public function __construct(RecurringBill $recurringBill)
    {
        $this->recurringBill = $recurringBill;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->recurringBill->id))->dontRelease()];
    }

    public function handle(RecurringBill $recurringBill): void
    {
        $stats = [
            'number_pallets'                => RecurringBillTransaction::where('recurring_bill_id', $recurringBill->id)->where('item_type', 'Pallet')->count(),
            'number_stored_items'           => RecurringBillTransaction::where('recurring_bill_id', $recurringBill->id)->where('item_type', 'StoreItem')->count(),
            'number_pallet_deliveries'      => $recurringBill->palletDelivery()->count(),
            'number_pallet_returns'         => $recurringBill->palletReturn()->count(),

        ];


        $recurringBill->stats()->update($stats);
    }
}
