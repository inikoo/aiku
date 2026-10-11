<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Notifications;

use App\Models\GoodsIn\StockDelivery;
use Illuminate\Notifications\Notification;

class StockDeliveryReceivedWithoutCostsNotification extends Notification
{
    public function __construct(public StockDelivery $stockDelivery, public string $userName, public string $missing, public string $reason)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => __(':reference was received without its costs', ['reference' => $this->stockDelivery->reference]),
            'body'  => __(':user received it. Reason: :reason. Missing: :missing. Its goods can not be put away until the costs are in.', [
                'user'    => $this->userName,
                'reason'  => $this->reason,
                'missing' => $this->missing,
            ]),
            'type'  => 'stock_delivery_received_without_costs',
            'slug'  => (string) $this->stockDelivery->id,
            'route' => [
                'name'       => 'grp.org.procurement.stock_deliveries.show',
                'parameters' => [
                    $this->stockDelivery->organisation->slug,
                    $this->stockDelivery->slug,
                ],
            ],
        ];
    }
}
