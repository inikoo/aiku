<?php
/*
 *  Author: Jonathan lopez <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, inikoo
 */

namespace App\Http\Resources\Mail;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property integer $number_outboxes
 * @property integer $number_mailshots
 * @property integer $number_dispatched_emails
 * @property string $code
 * @property mixed $created_at
 * @property mixed $updated_at
 *
 */
class PostRoomResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'number_outboxes'          => $this->number_outboxes,
            'number_mailshots'         => $this->number_mailshots,
            'number_dispatched_emails' => $this->number_dispatched_emails,
            'code'                     => $this->code,
            'created_at'               => $this->created_at,
            'updated_at'               => $this->updated_at,
        ];
    }
}
