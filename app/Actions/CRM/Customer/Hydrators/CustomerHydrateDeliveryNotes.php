<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 15 Sept 2024 21:29:12 Malaysia Time, Taipei, Taiwan
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Actions\CRM\Customer\Hydrators;

use App\Actions\Traits\Hydrators\WithHydrateDeliveryNotes;
use App\Actions\Traits\WithEnumStats;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStateEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteStatusEnum;
use App\Enums\Dispatching\DeliveryNote\DeliveryNoteTypeEnum;
use App\Models\CRM\Customer;
use App\Models\Dispatching\DeliveryNote;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Lorisleiva\Actions\Concerns\AsAction;

class CustomerHydrateDeliveryNotes
{
    use AsAction;
    use WithEnumStats;
    use WithHydrateDeliveryNotes;

    private Customer $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function getJobMiddleware(): array
    {
        return [(new WithoutOverlapping($this->customer->id))->dontRelease()];
    }

    public function handle(Customer $customer): void
    {

        $stats = $this->getDeliveryNotesStats($customer);

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'delivery_notes',
                field: 'type',
                enum: DeliveryNoteTypeEnum::class,
                models: DeliveryNote::class,
                where: function ($q) use ($customer) {
                    $q->where('customer_id', $customer->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'delivery_notes',
                field: 'state',
                enum: DeliveryNoteStateEnum::class,
                models: DeliveryNote::class,
                where: function ($q) use ($customer) {
                    $q->where('customer_id', $customer->id);
                }
            )
        );

        $stats = array_merge(
            $stats,
            $this->getEnumStats(
                model: 'delivery_notes',
                field: 'status',
                enum: DeliveryNoteStatusEnum::class,
                models: DeliveryNote::class,
                where: function ($q) use ($customer) {
                    $q->where('customer_id', $customer->id);
                }
            )
        );

        $customer->stats()->update($stats);
    }

}
