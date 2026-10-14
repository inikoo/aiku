<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Wed, 14 Oct 2026 13:50:00 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Notifications;

use App\Models\GoodsIn\StockDelivery;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Notifications\Notification;

class AgreedTermDifferenceAcceptedNotification extends Notification
{
    public function __construct(public PurchaseOrder|StockDelivery $document, public string $userName, public string $titles)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => __(':reference differs from what was agreed', ['reference' => $this->document->reference]),
            'body'  => __(':user accepted: :titles', ['user' => $this->userName, 'titles' => $this->titles]),
            'type'  => 'agreed_term_difference_accepted',
            'slug'  => (string) $this->document->id,
            'route' => [
                'name'       => $this->document instanceof PurchaseOrder ? 'grp.org.procurement.purchase_orders.show' : 'grp.org.procurement.stock_deliveries.show',
                'parameters' => [
                    $this->document->organisation->slug,
                    $this->document->slug,
                ],
            ],
        ];
    }
}
