<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Mon, 04 Dec 2023 16:46:55 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Http\Resources\SysAdmin;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $email
 */
class InertiaTableWebUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'email'      => $this->email,


        ];
    }
}
