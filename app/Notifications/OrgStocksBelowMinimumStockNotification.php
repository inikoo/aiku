<?php

/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Mon, 19 Oct 2026 16:00:00 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Notifications;

use App\Models\Inventory\Warehouse;
use Illuminate\Notifications\Notification;

class OrgStocksBelowMinimumStockNotification extends Notification
{
    private const int CODES_SHOWN = 25;

    /**
     * @param  array<int, string>  $codes  the SKOs that have just fallen below their minimum, largest shortfall first
     */
    public function __construct(public Warehouse $warehouse, public array $codes)
    {
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $shown = implode(', ', array_slice($this->codes, 0, self::CODES_SHOWN));
        $more  = count($this->codes) - self::CODES_SHOWN;

        return [
            'title' => trans_choice('{1} 1 SKO fell below its minimum stock|[2,*] :count SKOs fell below their minimum stock', count($this->codes)),
            'body'  => $more > 0 ? __(':codes and :more more', ['codes' => $shown, 'more' => $more]) : $shown,
            'type'  => 'org_stocks_below_minimum_stock',
            'slug'  => $this->warehouse->slug,
            'route' => [
                'name'       => 'grp.org.warehouses.show.inventory.org_stocks.below_minimum_stock.index',
                'parameters' => [
                    $this->warehouse->organisation->slug,
                    $this->warehouse->slug,
                ],
            ],
        ];
    }
}
