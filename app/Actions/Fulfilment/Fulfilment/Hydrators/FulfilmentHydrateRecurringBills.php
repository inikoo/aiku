<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 27 Apr 2024 19:22:49 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\RecurringBill\RecurringBillStatusEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\RecurringBill;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class FulfilmentHydrateRecurringBills
{
    use AsAction;
    use WithEnumStats;

    private Fulfilment $fulfilment;

    public function __construct(Fulfilment $fulfilment)
    {
        $this->fulfilment = $fulfilment;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->fulfilment->id))->dontRelease()];
    }

    public function handle(Fulfilment $fulfilment): void
    {
        $stats = [
            'number_recurring_bills' => $fulfilment->recurringBills()->count(),
        ];


        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'recurring_bills',
                field: 'status',
                enum: RecurringBillStatusEnum::class,
                models: RecurringBill::class,
                where: function ($q) use ($fulfilment) {
                    $q->where('fulfilment_id', $fulfilment->id);
                }
            )
        );

        $fulfilment->stats()->update($stats);
    }


}
