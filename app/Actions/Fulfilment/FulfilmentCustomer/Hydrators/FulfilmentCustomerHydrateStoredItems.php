<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 26 Jan 2024 19:23:44 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\Fulfilment\FulfilmentCustomer\Hydrators;

use App\Actions\Traits\WithEnumStats;
use App\Enums\Fulfilment\StoredItem\StoredItemStateEnum;
use App\Models\Fulfilment\FulfilmentCustomer;
use App\Models\Fulfilment\StoredItem;
use Lorisleiva\Actions\Concerns\AsAction;

class FulfilmentCustomerHydrateStoredItems
{
    use AsAction;
    use WithEnumStats;

    public function handle(FulfilmentCustomer $fulfilmentCustomer): void
    {

        $stats = [
            'number_stored_items'        => $fulfilmentCustomer->storedItems()->count(),
        ];

        $stats=array_merge($stats, $this->getEnumStats(
            model:'stored_items',
            field: 'state',
            enum: StoredItemStateEnum::class,
            models: StoredItem::class,
            where: function ($q) use ($fulfilmentCustomer) {
                $q->where('fulfilment_customer_id', $fulfilmentCustomer->id);
            }
        ));

        $fulfilmentCustomer->update($stats);
    }


}
