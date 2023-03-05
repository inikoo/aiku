<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Sun, 30 Oct 2022 02:50:05 Greenwich Mean Time, Plane HK-KL
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Resources\Web;

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
