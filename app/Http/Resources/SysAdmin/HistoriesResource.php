<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 24-12-2024, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2024
 *
*/

namespace App\Http\Resources\SysAdmin;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * @property mixed $id
 * @property mixed $slug
 * @property mixed $code
 * @property mixed $contact_name
 * @property mixed $email
 * @property mixed $username
 */
class HistoriesResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        return [
            'id'           => $this->id,
            'datetime'     => $this->created_at, // Assuming you have a created_at field
            'user_name'    => $this->username,
            'old_values'   => $this->old_values, // Assuming you have an old_values field
            'new_values'   => $this->new_values, // Assuming you have a new_values field
            'event'        => $this->event, // Assuming you have an event field
        ];
    }
}
