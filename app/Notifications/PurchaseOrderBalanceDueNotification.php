<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Notifications;

use App\Notifications\Concerns\SendsMailThroughSes;
use App\Models\Procurement\PurchaseOrder;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderBalanceDueNotification extends Notification
{
    use SendsMailThroughSes;

    public bool $byEmail = true;

    public function __construct(public PurchaseOrder $purchaseOrder, public string $balance)
    {
    }

    public function via($notifiable): array
    {
        return $this->byEmail ? ['database', 'mail'] : ['database'];
    }

    public function title(): string
    {
        return __(':reference passed its QC: the balance of :amount can be paid', ['reference' => $this->purchaseOrder->reference, 'amount' => $this->balance]);
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title(),
            'body'  => __('Record the payment on the order with its bank statement.'),
            'type'  => 'purchase_order_balance_due',
            'slug'  => (string) $this->purchaseOrder->id,
            'route' => $this->route(),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return $this->newMailMessage()
            ->subject($this->title())
            ->line($this->title())
            ->line(__('Record the payment on the order with its bank statement.'))
            ->action(__('Open the order'), route($this->route()['name'], $this->route()['parameters']));
    }

    public function route(): array
    {
        return [
            'name'       => 'grp.org.procurement.purchase_orders.show',
            'parameters' => [$this->purchaseOrder->organisation->slug, $this->purchaseOrder->slug],
        ];
    }
}
