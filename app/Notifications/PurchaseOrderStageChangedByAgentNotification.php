<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Mijas, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Notifications;

use App\Models\Procurement\PurchaseOrder;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderStageChangedByAgentNotification extends Notification
{
    public function __construct(public PurchaseOrder $purchaseOrder, public string $agentUserName, public string $change)
    {
    }

    public bool $byEmail = true;

    public function via($notifiable): array
    {
        return $this->byEmail ? ['database', 'mail'] : ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => __(':reference: :change', ['reference' => $this->purchaseOrder->reference, 'change' => $this->change]),
            'body'  => __(':user, from the agent, recorded it on the order.', ['user' => $this->agentUserName]),
            'type'  => 'purchase_order_stage_changed_by_agent',
            'slug'  => (string) $this->purchaseOrder->id,
            'route' => $this->route(),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject(__(':reference: :change', ['reference' => $this->purchaseOrder->reference, 'change' => $this->change]))
            ->line(__(':user, from the agent, recorded on :reference: :change.', [
                'user'      => $this->agentUserName,
                'reference' => $this->purchaseOrder->reference,
                'change'    => $this->change,
            ]))
            ->action(__('Open the order'), route($this->route()['name'], $this->route()['parameters']))
            ->line(__('You get this email because you are the buyer of this order.'));
    }

    private function route(): array
    {
        return [
            'name'       => 'grp.org.procurement.purchase_orders.show',
            'parameters' => [$this->purchaseOrder->organisation->slug, $this->purchaseOrder->slug],
        ];
    }
}
