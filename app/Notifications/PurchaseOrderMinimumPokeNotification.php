<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Notifications;

use App\Models\Procurement\PurchaseOrder;
use Illuminate\Notifications\Notification;

/**
 * Shown in aiku straight away; email and Slack come later grouped by SendPurchaseOrderMinimumPokeDigests.
 */
class PurchaseOrderMinimumPokeNotification extends Notification
{
    public function __construct(public PurchaseOrder $purchaseOrder, public string $title, public string $body)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'type'  => 'purchase_order_minimum_poke',
            'slug'  => (string) $this->purchaseOrder->id,
            'route' => [
                'name'       => 'grp.org.procurement.purchase_orders.show',
                'parameters' => [$this->purchaseOrder->organisation->slug, $this->purchaseOrder->slug],
            ],
        ];
    }
}
