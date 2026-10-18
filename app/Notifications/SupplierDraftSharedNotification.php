<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sat, 10 Oct 2026 Malaga, Spain
 * Copyright (c) 2026, Raul A Perusquia Flores
 */

namespace App\Notifications;

use App\Models\Procurement\OrgAgent;
use Illuminate\Notifications\Notification;

class SupplierDraftSharedNotification extends Notification
{
    public function __construct(public OrgAgent $orgAgent, public string $title, public string $note)
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
            'body'  => $this->note,
            'type'  => 'supplier_draft_shared',
            'slug'  => (string) $this->orgAgent->id,
            'route' => [
                'name'       => 'grp.org.procurement.org_agents.show.shopping.drafts.index',
                'parameters' => [
                    $this->orgAgent->organisation->slug,
                    $this->orgAgent->slug,
                ],
            ],
        ];
    }
}
