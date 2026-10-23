<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 11 Oct 2026 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Notifications;

use App\Models\GoodsIn\StockDelivery;
use App\Notifications\Concerns\SendsMailThroughSes;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StockDeliveryBookedInWithoutCostsNotification extends Notification
{
    use SendsMailThroughSes;

    public function __construct(public StockDelivery $stockDelivery, public string $missing)
    {
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function title(): string
    {
        return __('Costing late: :reference is being booked in and its costs are not entered', ['reference' => $this->stockDelivery->reference]);
    }

    public function body(): string
    {
        return __('The warehouse is putting the goods of :reference away now. Its costs should have been entered before the goods arrived. Still missing: :missing. Until they are in, this stock is valued at the purchase order price and its margins are wrong. Enter them today: an amount, an estimate or not applicable.', [
            'reference' => $this->stockDelivery->reference,
            'missing'   => $this->missing,
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title(),
            'body'  => $this->body(),
            'type'  => 'stock_delivery_booked_in_without_costs',
            'slug'  => (string) $this->stockDelivery->id,
            'route' => $this->route(),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return $this->newMailMessage()
            ->subject($this->title())
            ->line($this->body())
            ->action(__('Enter the costs'), route($this->route()['name'], $this->route()['parameters']));
    }

    public function route(): array
    {
        return [
            'name'       => 'grp.org.procurement.stock_deliveries.show',
            'parameters' => [$this->stockDelivery->organisation->slug, $this->stockDelivery->slug],
        ];
    }
}
