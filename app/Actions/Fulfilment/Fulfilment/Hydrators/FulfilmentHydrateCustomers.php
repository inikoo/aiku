<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 14 May 2024 15:19:54 British Summer Time, Sheffield, UK
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\Fulfilment\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\FulfilmentCustomer\FulfilmentCustomerStatusEnum;
use App\Models\Fulfilment\Fulfilment;
use App\Models\Fulfilment\FulfilmentCustomer;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class FulfilmentHydrateCustomers
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
            'number_customers_interest_pallets_storage' => $fulfilment->fulfilmentCustomers()->where('pallets_storage', true)->count(),
            'number_customers_interest_items_storage'   => $fulfilment->fulfilmentCustomers()->where('items_storage', true)->count(),
            'number_customers_interest_dropshipping'    => $fulfilment->fulfilmentCustomers()->where('dropshipping', true)->count(),

        ];

        $stats=array_merge($stats, $this->getEnumStats(
            model:'customers',
            field: 'status',
            enum: FulfilmentCustomerStatusEnum::class,
            models: FulfilmentCustomer::class,
            where: function ($q) use ($fulfilment) {
                $q->where('fulfilment_id', $fulfilment->id);
            }
        ));

        $fulfilment->stats()->update($stats);
    }


}
