<?php
/*
 *  Author: Jonathan lopez <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, inikoo
 */

namespace App\Http\Resources\Mail;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $state
 * @property string $data
 * @property string $id
 * @property mixed $created_at
 * @property mixed $updated_at

 *
 */
class MailshotResource extends JsonResource
{
    public function toArray($request): array
    {
        return array(
            'data'                           => $this->data,
            'id'                             => $this->id,
            'state'                          => $this->state,
            'created_at'                     => $this->created_at,
            'updated_at'                     => $this->updated_at,
        );
    }
}
