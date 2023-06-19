<?php
/*
 *  Author: Jonathan lopez <raul@inikoo.com>
 *  Created: Sat, 22 Oct 2022 18:53:15 British Summer Time, Sheffield, UK
 *  Copyright (c) 2022, inikoo
 */

namespace App\Http\Resources\UniversalSearch;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string $id
 * @property string $secondary_term
 * @property mixed $created_at
 * @property mixed $updated_at
 * @property string $primary_term
 * @property string $model_id
 * @property string $model_type
 * @property string $icon
 * @property string $route
 * @property string $section
 */

class UniversalSearchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'model_type' => $this->model_type,
            'model_id' => $this->model_id,
            'section'  => $this->section,
            'icon'  => $this->icon,
            'route' => json_decode($this->route, true),
            'primary_term' => $this->primary_term,
            'secondary_term' => $this->secondary_term,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
