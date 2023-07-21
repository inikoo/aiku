<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 21:10:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\UI;

use App\Http\Resources\HasSelfCall;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $username
 * @property string $email
 * @property mixed $avatar_id
 */
class LoggedUserResource extends JsonResource
{
    use HasSelfCall;

    public function toArray($request): array
    {
        return [
            'username'  => $this->username,
            'email'     => $this->email,
            'avatar_id' => $this->avatar_id

        ];
    }
}
