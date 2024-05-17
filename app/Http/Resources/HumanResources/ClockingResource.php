<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Thu, 21 Oct 2021 12:37:51 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2021, Inikoo
 *  Version 4.0
 */

namespace App\Http\Resources\HumanResources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $slug
 * @property string $type
 * @property string $notes
 * @property string $attend_at
 * @property string $workplace_slug
 * @property string $clocking_machine_slug
 */
class ClockingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                    => $this->id,
            'type'                  => $this->type,
            'notes'                 => $this->notes,
            'workplace_slug'        => $this->workplace->slug,
            'attend_at'             => $this->attend_at,
            'clocking_machine_slug' => $this->clockingMachine->slug,
            'employee'              => EmployeeResource::make($this->subject),
            'photo'                 => $this->photoImageSources()
        ];
    }
}
