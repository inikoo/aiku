<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 16 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Events;

use App\Actions\Procurement\PurchaseOrder\UI\GetAgentIncomingPurchaseOrdersCount;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;

class BroadcastAgentIncomingPurchaseOrdersChanged implements ShouldBroadcast
{
    use Dispatchable;
    use InteractsWithSockets;

    public bool $afterCommit = true;

    /**
     * @param  array{purchase_order_id: int, reference: string, client: string, amount: float, currency: string, url: string}|null  $newPurchaseOrder
     */
    public function __construct(public int $agentOrganisationId, public ?array $newPurchaseOrder = null)
    {
    }

    public static function dispatchFor(PurchaseOrder $purchaseOrder): void
    {
        if ($agentOrganisationId = $purchaseOrder->agent?->organisation_id) {
            self::dispatch($agentOrganisationId);
        }
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel(GetAgentIncomingPurchaseOrdersCount::channel($this->agentOrganisationId))];
    }

    public function broadcastAs(): string
    {
        return 'incoming-purchase-orders-changed';
    }

    public function broadcastWith(): array
    {
        return ['new_purchase_order' => $this->newPurchaseOrder];
    }
}
