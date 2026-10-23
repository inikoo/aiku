<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 23 Oct 2026 Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Notifications;

use App\Models\Procurement\PurchaseOrder;
use App\Models\Procurement\PurchaseOrderTransaction;
use App\Notifications\Concerns\SendsMailThroughSes;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderQuantityChangeAnsweredNotification extends Notification
{
    use SendsMailThroughSes;

    public bool $byEmail = true;

    public function __construct(public PurchaseOrder $purchaseOrder, public PurchaseOrderTransaction $purchaseOrderTransaction, public string $agentOrganisationSlug)
    {
    }

    public function via($notifiable): array
    {
        return $this->byEmail ? ['database', 'mail'] : ['database'];
    }

    public function title(): string
    {
        return __(':reference: :code, :answer', [
            'reference' => $this->purchaseOrder->reference,
            'code'      => $this->purchaseOrderTransaction->supplierProduct?->code ?? $this->purchaseOrderTransaction->orgStock?->code,
            'answer'    => $this->purchaseOrderTransaction->quantity_query_state->label(),
        ]);
    }

    public function body(): string
    {
        return __(':organisation answered. The line now orders :quantity units.', [
            'organisation' => $this->purchaseOrder->organisation->name,
            'quantity'     => trimDecimalZeros($this->purchaseOrderTransaction->quantity_ordered),
        ]);
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title(),
            'body'  => $this->body(),
            'type'  => 'purchase_order_quantity_answered',
            'slug'  => (string) $this->purchaseOrder->id,
            'route' => $this->route(),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return $this->newMailMessage()
            ->subject($this->title())
            ->line($this->body())
            ->action(__('Open the order'), route($this->route()['name'], $this->route()['parameters']))
            ->line(__('You get this email because you asked for this quantity.'));
    }

    private function route(): array
    {
        return [
            'name'       => 'grp.org.agent.purchase_orders.show',
            'parameters' => [$this->agentOrganisationSlug, $this->purchaseOrder->slug],
        ];
    }
}
