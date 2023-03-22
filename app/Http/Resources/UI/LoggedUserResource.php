<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Tue, 21 Mar 2023 21:10:46 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\UI;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

/**
 * @property string $username
 * @property string $email
 * @property array $data
 */
class LoggedUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'username' => $this->username,
            'email'    => $this->email,
            'avatar'   => Arr::get($this->data, 'avatar'),
        ];
    }
}
