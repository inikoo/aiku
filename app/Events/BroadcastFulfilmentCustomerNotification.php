<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 07 Dec 2023 14:06:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Events;

use App\Actions\Fulfilment\StoredItemReturn\StoreStoredItemReturn;
use App\Models\Fulfilment\PalletDelivery;
use App\Models\Fulfilment\PalletReturn;
use App\Models\SysAdmin\Group;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BroadcastFulfilmentCustomerNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public array $data;
    public Group $group;
    public PalletDelivery|PalletReturn|StoreStoredItemReturn $parent;

    public function __construct(Group $group, PalletDelivery|PalletReturn|StoreStoredItemReturn $parent)
    {
        $this->parent = $parent;
        $this->group  = $group;
        $this->data   = [
            'title' => $parent->state->notifications($parent->reference)[$parent->state->value]['title'],
            'body'  => $parent->state->notifications($parent->reference)[$parent->state->value]['subtitle'],
            'type'  => class_basename($parent),
            'slug'  => $parent->slug,
            'id'    => $parent->id,
            'route' => match (class_basename($parent)) {
                'PalletDelivery' => route('grp.org.fulfilments.show.crm.customers.show.pallet-deliveries.show', [
                    'organisation'       => $parent->organisation->slug,
                    'fulfilment'         => $parent->fulfilment->slug,
                    'fulfilmentCustomer' => $parent->fulfilmentCustomer->slug,
                    $parent->slug
                ]),
                'PalletReturn' => route('grp.org.fulfilments.show.crm.customers.show.pallet-returns.show', [
                    'organisation'       => $parent->organisation->slug,
                    'fulfilment'         => $parent->fulfilment->slug,
                    'fulfilmentCustomer' => $parent->fulfilmentCustomer->slug,
                    $parent->slug
                ]),
                'StoreStoredItemReturn' => route('grp.org.fulfilments.show.crm.customers.show.stored-item-returns.show', [
                    'organisation'       => $parent->organisation->slug,
                    'fulfilment'         => $parent->fulfilment->slug,
                    'fulfilmentCustomer' => $parent->fulfilmentCustomer->slug,
                    $parent->slug
                ]),
                default => null
            }
        ];
    }

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("grp.".$this->group->id.".fulfilmentCustomer.{$this->parent->fulfilmentCustomer->id}")
        ];
    }

    public function broadcastAs(): string
    {
        return class_basename($this->parent);
    }
}
