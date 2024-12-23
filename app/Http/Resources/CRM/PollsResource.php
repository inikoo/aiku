<?php
/*
 * author Arya Permana - Kirin
 * created on 23-12-2024-15h-21m
 * github: https://github.com/KirinZero0
 * copyright 2024
*/


namespace App\Http\Resources\CRM;

use Illuminate\Http\Resources\Json\JsonResource;

class PollsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                 => $this->id,
            'slug'               => $this->slug,
            'name'               => $this->name,
            'label'              => $this->label,
            'position'           => $this->position,
            'type'               => $this->type,
        ];
    }
}