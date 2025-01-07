<?php

/*
 * Author: Ganes <gustiganes@gmail.com>
 * Created on: 07-01-2025, Bali, Indonesia
 * Github: https://github.com/Ganes556
 * Copyright: 2025
 *
*/

namespace App\Http\Resources\Fulfilment;

use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

class FulfilmentCustomerNoteResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        return [
            'author' => $this->author,
            'note' => $this->note,
            'datetime' => $this->datetime,
            'details' => ($this->details_text) ? $this->details_text : $this->details_html,
        ];
    }
}
