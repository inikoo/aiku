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

class ComplianceDocumentsReminderNotification extends Notification
{
    use SendsMailThroughSes;

    /**
     * @param string[] $missing
     */
    public function __construct(public PurchaseOrder $purchaseOrder, public array $missing, public string $deadline, public string $occasion)
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
            'title' => __(':reference: compliance documents due :date', ['reference' => $this->purchaseOrder->reference, 'date' => $this->deadline]),
            'body'  => $this->occasion.' '.__('Still missing: :documents.', ['documents' => implode(', ', $this->missing)]),
            'type'  => 'purchase_order_compliance_reminder',
            'slug'  => (string) $this->purchaseOrder->id,
            'route' => $this->route(),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $message = $this->newMailMessage()
            ->subject(__(':reference: compliance documents due :date', ['reference' => $this->purchaseOrder->reference, 'date' => $this->deadline]))
            ->line($this->occasion)
            ->line(__('Still missing:'));

        foreach ($this->missing as $document) {
            $message->line('- '.$document);
        }

        return $message
            ->action(__('Open the order'), route($this->route()['name'], $this->route()['parameters']).'?tab=compliance')
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
