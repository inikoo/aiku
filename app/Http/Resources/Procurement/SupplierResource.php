<?php
/*
 * Author: Jonathan Lopez Sanchez <jonathan@ancientwisdom.biz>
 * Created: Fri, 24 Feb 2023 10:14:02 Central European Standard Time, Malaga, Spain
 * Copyright (c) 2023, Inikoo LTD
 */

namespace App\Http\Resources\Procurement;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $code
 * @property string $name
 * @property string $slug
 * @property string $created_at
 * @property string $updated_at
 * @property string $agent_slug
 */
class SupplierResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'agent_slug' => $this->agent_slug,
            'code'       => $this->code,
            'name'       => $this->name,
            'slug'       => $this->slug,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
