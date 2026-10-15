<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Thu, 15 Oct 2026 Kuala Lumpur, Malaysia
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PurchaseOrderMinimumPokeDigestNotification extends Notification
{
    /**
     * @param array<int, array{line: string, url: string}> $requests
     */
    public function __construct(public array $requests)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage())
            ->subject(trans_choice('A colleague asks you to add to your draft to reach a supplier minimum|:count requests to add to your drafts to reach supplier minimums', count($this->requests), ['count' => count($this->requests)]));

        foreach ($this->requests as $request) {
            $mail->line($request['line'].' '.$request['url']);
        }

        return $mail->line(__('You get this email because you are the buyer of these draft purchase orders. You can change how you hear about these requests on your profile.'));
    }
}
