<?php

/*
 *  Author: Jonathan lopez <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, inikoo
 */

namespace App\Http\Resources\Mail;

use App\Enums\Comms\Outbox\OutboxTypeEnum;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $slug
 * @property string $data
 * @property string $name
 * @property OutboxTypeEnum $type
 * @property mixed $created_at
 * @property mixed $updated_at
 *
 */
class OutboxResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'slug'                           => $this->slug,
            'data'                           => $this->data,
            'name'                           => $this->name,
            'type'                           => $this->type->stateIcon()[$this->type->value],
            'total_mailshots'                => $this->total_mailshots,
            'dispatched_emails_lw'           => $this->dispatched_emails_lw,
            'opened_emails_lw'               => $this->opened_emails_lw,
            'unsubscribed_emails_lw'         => $this->unsubscribed_emails_lw,
            'created_at'                     => $this->created_at,
            'updated_at'                     => $this->updated_at,
        ];
    }
}
