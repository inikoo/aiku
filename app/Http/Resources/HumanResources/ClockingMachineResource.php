<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 21 Oct 2021 12:37:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Http\Resources\HumanResources;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * @property int $id
 * @property string $slug
 * @property string $code
 * @property string $workplace_slug
 */
class ClockingMachineResource extends JsonResource
{
    public function toArray($request): array|Arrayable|JsonSerializable
    {
        return [
            'id'                => $this->id,
            'slug'              => $this->slug,
            'code'              => $this->code,
            'workplace_slug'    => $this->workplace_slug,
        ];
    }
}
