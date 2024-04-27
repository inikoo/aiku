<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 19:27:30 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\RecurringBill\RecurringBillStatusEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\RecurringBill;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class FulfilmentCustomerHydrateRecurringBills
{
    use AsAction;
    use WithEnumStats;

    private FulfilmentCustomer $fulfilmentCustomer;

    public function __construct(FulfilmentCustomer $fulfilmentCustomer)
    {
        $this->fulfilmentCustomer = $fulfilmentCustomer;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->fulfilmentCustomer->id))->dontRelease()];
    }

    public function handle(FulfilmentCustomer $fulfilmentCustomer): void
    {
        $stats = [
            'number_recurring_bills' => $fulfilmentCustomer->recurringBills()->count(),
        ];


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'recurring_bills',
                field: 'status',
                enum: RecurringBillStatusEnum::class,
                models: RecurringBill::class,
                where: function ($q) use ($fulfilmentCustomer) {
                    $q->where('fulfilment_customer_id', $fulfilmentCustomer->id);
                }
            )
        );

        $fulfilmentCustomer->update($stats);
    }


}
